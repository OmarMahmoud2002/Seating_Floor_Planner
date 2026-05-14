<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportFloorplanPdfRequest;
use App\Models\Event;
use App\Models\Floorplan;
use App\Services\Exports\FloorplanPdfExportService;
use App\Services\Exports\GuestListExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function guests(Event $event, GuestListExportService $exportService): BinaryFileResponse
    {
        $this->authorize('view', $event);

        return $exportService->download($event);
    }

    public function floorplanPdf(
        ExportFloorplanPdfRequest $request,
        Floorplan $floorplan,
        FloorplanPdfExportService $exportService
    ): Response {
        $this->authorize('view', $floorplan);

        return $exportService->download($floorplan, (string) $request->validated('image_data'));
    }
}
