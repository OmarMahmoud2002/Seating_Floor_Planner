<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmGuestImportRequest;
use App\Http\Requests\ImportGuestsRequest;
use App\Models\Event;
use App\Services\Guests\GuestImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuestImportController extends Controller
{
    public function create(Event $event): View
    {
        $this->authorize('view', $event);

        return view('guests.import.create', [
            'event' => $event,
        ]);
    }

    public function preview(ImportGuestsRequest $request, Event $event, GuestImportService $importService): View
    {
        $this->authorize('view', $event);

        $file = $request->file('file');
        $token = (string) Str::uuid();
        $path = $file->storeAs('guest-imports', $token.'.'.$file->getClientOriginalExtension(), 'local');

        try {
            $preview = $importService->preview($event, $path);
        } catch (ValidationException $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        session()->put("guest_imports.{$token}", [
            'event_id' => $event->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return view('guests.import.preview', [
            'event' => $event,
            'token' => $token,
            'originalName' => $file->getClientOriginalName(),
            'preview' => $preview,
        ]);
    }

    public function store(ConfirmGuestImportRequest $request, Event $event, GuestImportService $importService): RedirectResponse
    {
        $this->authorize('view', $event);

        $token = (string) $request->validated('token');
        $payload = session("guest_imports.{$token}");

        if (! $payload || (int) $payload['event_id'] !== $event->id || ! Storage::disk('local')->exists($payload['path'])) {
            return redirect()
                ->route('events.guests.import.create', $event)
                ->withErrors(['file' => 'انتهت صلاحية معاينة الاستيراد، يرجى رفع الملف مرة أخرى.']);
        }

        $result = $importService->import($event, $payload['path']);

        Storage::disk('local')->delete($payload['path']);
        session()->forget("guest_imports.{$token}");

        $summary = $result['summary'];

        return redirect()
            ->route('events.guests.index', $event)
            ->with(
                'status',
                "تم استيراد {$summary['imported_rows']} ضيف. تم تخطي {$summary['duplicate_rows']} مكرر و{$summary['invalid_rows']} صف غير صالح."
            );
    }
}
