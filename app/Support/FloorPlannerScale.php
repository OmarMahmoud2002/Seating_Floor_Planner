<?php

namespace App\Support;

class FloorPlannerScale
{
    public const CM_PER_METER = 100;
    public const PIXELS_PER_METER = 80;
    public const SMALL_GRID_CM = 10;
    public const CHAIR_DIAMETER_CM = 45;

    public static function metersToPixels(float|int|string|null $meters): float
    {
        return max((float) ($meters ?? 0), 0) * self::PIXELS_PER_METER;
    }

    public static function cmToPixels(float|int|string|null $cm): float
    {
        return ((float) ($cm ?? 0) / self::CM_PER_METER) * self::PIXELS_PER_METER;
    }

    public static function pixelsToCm(float|int|string|null $pixels): float
    {
        return ((float) ($pixels ?? 0) / self::PIXELS_PER_METER) * self::CM_PER_METER;
    }

    /**
     * @param array<string, mixed> $element
     */
    public static function elementRect(array $element): array
    {
        $xCm = $element['xCm'] ?? self::pixelsToCm($element['x'] ?? 0);
        $yCm = $element['yCm'] ?? self::pixelsToCm($element['y'] ?? 0);
        $widthCm = $element['widthCm'] ?? self::pixelsToCm($element['width'] ?? 80);
        $heightCm = $element['heightCm'] ?? self::pixelsToCm($element['height'] ?? 50);

        return [
            'x' => self::cmToPixels($xCm),
            'y' => self::cmToPixels($yCm),
            'width' => max(self::cmToPixels($widthCm), 1),
            'height' => max(self::cmToPixels($heightCm), 1),
        ];
    }

    /**
     * @param array<string, mixed> $seat
     */
    public static function seatPosition(array $seat): array
    {
        return [
            'x' => self::cmToPixels($seat['xCm'] ?? $seat['x'] ?? 0),
            'y' => self::cmToPixels($seat['yCm'] ?? $seat['y'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $seat
     * @return array<string, float|int>
     */
    public static function guestLabelPosition(array $seat, ?string $shape): array
    {
        $seatPosition = self::seatPosition($seat);
        $width = 88;
        $height = 20;
        $rotation = abs(fmod((float) ($seat['rotation'] ?? 0), 180.0));
        $isHorizontalSeat = $rotation === 0.0;
        $alternatesVertically = in_array($shape, ['rectangle', 'square'], true) && $isHorizontalSeat;
        $isEvenSeat = ((int) ($seat['number'] ?? 0)) % 2 === 0;

        return [
            'x' => $seatPosition['x'] - ($width / 2),
            'y' => $alternatesVertically && $isEvenSeat
                ? $seatPosition['y'] + 18
                : $seatPosition['y'] - $height - 18,
            'width' => $width,
            'height' => $height,
        ];
    }

    public static function isRoundTable(?string $shape): bool
    {
        return in_array($shape, ['round', 'round-100', 'round-120'], true);
    }

    public static function diameterCm(?string $shape): ?int
    {
        return match ($shape) {
            'round', 'round-100' => 100,
            'round-120' => 120,
            default => null,
        };
    }
}
