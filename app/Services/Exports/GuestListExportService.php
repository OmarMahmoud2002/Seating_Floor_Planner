<?php

namespace App\Services\Exports;

use App\Exports\GuestListExport;
use App\Models\Event;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestListExportService
{
    public function download(Event $event): BinaryFileResponse
    {
        return Excel::download(
            new GuestListExport($event),
            $this->filename($event)
        );
    }

    private function filename(Event $event): string
    {
        return 'guest-list-event-'.$event->id.'.xlsx';
    }
}
