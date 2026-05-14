<?php

namespace App\Services\Exports;

use App\Models\Floorplan;
use App\Support\ArabicPdfText;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class FloorplanPdfExportService
{
    public function download(Floorplan $floorplan, string $imageData): Response
    {
        $floorplan->load(['event', 'seats.guest.guestType']);

        $seatCount = $floorplan->seats->count();
        $assignedCount = $floorplan->seats->whereNotNull('guest_id')->count();
        $typeSummary = $floorplan->seats
            ->filter(fn ($seat): bool => $seat->guest !== null)
            ->groupBy(fn ($seat): string => $seat->guest->guestType?->display_name_ar ?: 'بدون نوع')
            ->map(fn ($seats): int => $seats->count())
            ->sortKeys();
        [$imageWidth, $imageHeight] = $this->imageSize($imageData);

        $pdf = Pdf::loadView('exports.floorplan-pdf', [
            'event' => $floorplan->event,
            'floorplan' => $floorplan,
            'imageData' => $imageData,
            'imageBox' => $this->imageBox($imageWidth, $imageHeight, $floorplan->paper_size, $floorplan->orientation),
            'seatCount' => $seatCount,
            'assignedCount' => $assignedCount,
            'typeSummary' => $typeSummary,
            'generatedAt' => now(),
            'pdfText' => fn ($value): string => ArabicPdfText::make($value),
        ])->setPaper(strtolower($floorplan->paper_size ?: 'A3'), $floorplan->orientation);

        return $pdf->download('floorplan-'.$floorplan->id.'.pdf');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function imageSize(string $imageData): array
    {
        $separatorPosition = strpos($imageData, ',');

        if ($separatorPosition === false) {
            return [1600, 1000];
        }

        $binary = base64_decode(substr($imageData, $separatorPosition + 1), true);

        if ($binary === false) {
            return [1600, 1000];
        }

        $size = @getimagesizefromstring($binary);

        if (! is_array($size) || empty($size[0]) || empty($size[1])) {
            return [1600, 1000];
        }

        return [(int) $size[0], (int) $size[1]];
    }

    /**
     * @return array{width: int, height: int}
     */
    private function imageBox(int $sourceWidth, int $sourceHeight, ?string $paperSize, ?string $orientation): array
    {
        $limits = [
            'A2' => [
                'landscape' => ['width' => 1320, 'height' => 640],
                'portrait' => ['width' => 880, 'height' => 1020],
            ],
            'A3' => [
                'landscape' => ['width' => 1040, 'height' => 500],
                'portrait' => ['width' => 700, 'height' => 780],
            ],
            'A4' => [
                'landscape' => ['width' => 720, 'height' => 330],
                'portrait' => ['width' => 500, 'height' => 600],
            ],
        ];

        $paper = $limits[$paperSize ?: 'A3'] ?? $limits['A3'];
        $box = $paper[$orientation ?: 'landscape'] ?? $paper['landscape'];
        $ratio = min($box['width'] / max($sourceWidth, 1), $box['height'] / max($sourceHeight, 1), 1);

        return [
            'width' => max((int) round($sourceWidth * $ratio), 1),
            'height' => max((int) round($sourceHeight * $ratio), 1),
        ];
    }
}
