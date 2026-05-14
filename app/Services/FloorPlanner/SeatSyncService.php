<?php

namespace App\Services\FloorPlanner;

use App\Models\Floorplan;
use App\Models\Seat;
use Illuminate\Support\Arr;

class SeatSyncService
{
    /**
     * @param array<string, mixed> $designJson
     */
    public function sync(Floorplan $floorplan, array $designJson): void
    {
        $seatRows = $this->extractSeats($designJson);
        $seatKeys = array_column($seatRows, 'seat_key');

        foreach ($seatRows as $seatRow) {
            Seat::query()->updateOrCreate(
                [
                    'floorplan_id' => $floorplan->id,
                    'seat_key' => $seatRow['seat_key'],
                ],
                Arr::except($seatRow, ['seat_key'])
            );
        }

        $query = Seat::query()->where('floorplan_id', $floorplan->id);

        if ($seatKeys === []) {
            $query->delete();

            return;
        }

        $query->whereNotIn('seat_key', $seatKeys)->delete();
    }

    /**
     * @param array<string, mixed> $designJson
     * @return array<int, array<string, mixed>>
     */
    private function extractSeats(array $designJson): array
    {
        $rows = [];
        $elements = $designJson['elements'] ?? [];

        if (! is_array($elements)) {
            return $rows;
        }

        foreach ($elements as $element) {
            if (! is_array($element) || ($element['type'] ?? null) !== 'table') {
                continue;
            }

            $seats = $element['seats'] ?? [];

            if (! is_array($seats)) {
                continue;
            }

            $tableKey = (string) ($element['id'] ?? '');

            if ($tableKey === '') {
                continue;
            }

            $tableX = (float) ($element['x'] ?? 0);
            $tableY = (float) ($element['y'] ?? 0);
            $tableRotation = (float) ($element['rotation'] ?? 0);
            $tableName = isset($element['label']) ? (string) $element['label'] : null;
            $shape = isset($element['tableShape']) ? (string) $element['tableShape'] : 'rectangle';

            foreach (array_values($seats) as $index => $seat) {
                if (! is_array($seat)) {
                    continue;
                }

                $seatNumber = (int) ($seat['number'] ?? ($index + 1));
                $seatKey = (string) ($seat['key'] ?? "{$tableKey}-seat-{$seatNumber}");

                $rows[] = [
                    'seat_key' => $seatKey,
                    'table_key' => $tableKey,
                    'table_name' => $tableName,
                    'seat_number' => $seatNumber,
                    'x' => round($tableX + (float) ($seat['x'] ?? 0), 2),
                    'y' => round($tableY + (float) ($seat['y'] ?? 0), 2),
                    'rotation' => round($tableRotation + (float) ($seat['rotation'] ?? 0), 2),
                    'metadata' => [
                        'shape' => $shape,
                        'relative_x' => round((float) ($seat['x'] ?? 0), 2),
                        'relative_y' => round((float) ($seat['y'] ?? 0), 2),
                        'label' => isset($seat['label']) ? (string) $seat['label'] : (string) $seatNumber,
                    ],
                ];
            }
        }

        return $rows;
    }
}
