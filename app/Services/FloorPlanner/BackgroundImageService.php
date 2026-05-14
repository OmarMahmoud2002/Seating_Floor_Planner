<?php

namespace App\Services\FloorPlanner;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackgroundImageService
{
    public function store(UploadedFile $file): string
    {
        $directory = 'floorplans/backgrounds';
        $extension = $this->extensionForMime((string) $file->getMimeType(), $file->extension());
        $path = $directory.'/'.Str::uuid().'.'.$extension;

        $optimized = $this->optimizeWithoutResizing($file, (string) $file->getMimeType());

        if ($optimized !== null) {
            Storage::disk('public')->put($path, $optimized);

            return $path;
        }

        return $file->storeAs($directory, basename($path), 'public');
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function optimizeWithoutResizing(UploadedFile $file, string $mime): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if (! $image) {
            return null;
        }

        ob_start();

        try {
            $success = match ($mime) {
                'image/jpeg' => $this->saveJpeg($image),
                'image/png' => imagepng($image, null, 6),
                'image/webp' => function_exists('imagewebp') && imagewebp($image, null, 88),
                default => false,
            };

            $optimized = ob_get_clean();
        } finally {
            imagedestroy($image);
        }

        if (! $success || $optimized === false || $optimized === '') {
            return null;
        }

        return $optimized;
    }

    /**
     * @param resource|\GdImage $image
     */
    private function saveJpeg($image): bool
    {
        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        imageinterlace($image, true);

        return imagejpeg($image, null, 88);
    }

    private function extensionForMime(string $mime, string $fallback): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $fallback,
        };
    }
}
