<?php

namespace App\Modules\Base\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

trait FileTrait
{
    final public function image(UploadedFile $image, string $folder, int $maxDimension = 1600): string
    {
        $optimized = $this->optimizeImage($image, $maxDimension);

        if ($optimized !== null) {
            $path = trim($folder, '/') . '/' . $image->hashName();
            Storage::disk('public')->put($path, $optimized);

            return $path;
        }

        $path = Storage::disk('public')->putFile($folder, $image);

        if (! is_string($path)) {
            throw new RuntimeException('The uploaded file could not be stored.');
        }

        return $path;
    }

    /**
     * Downscale oversized raster images and re-encode them with sane quality.
     * Returns null when the file is not a processable image or GD is unavailable,
     * so callers transparently fall back to storing the original file untouched.
     */
    private function optimizeImage(UploadedFile $image, int $maxDimension): ?string
    {
        if (! str_starts_with((string) $image->getMimeType(), 'image/')) {
            return null;
        }

        try {
            $picture = ImageManager::gd()->read($image->getRealPath());

            if ($picture->width() > $maxDimension || $picture->height() > $maxDimension) {
                $picture->scaleDown($maxDimension, $maxDimension);
            }

            return (string) $picture->encodeByExtension(
                $image->getClientOriginalExtension() ?: 'jpg',
                quality: 82,
            );
        } catch (Throwable) {
            return null;
        }
    }

    final public function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    final public function safeDeleteFile(?string $path, string $context, array $logData = []): void
    {
        if ($path === null || $path === '') {
            return;
        }

        try {
            $this->deleteFile($path);
        } catch (Throwable $exception) {
            Log::error("{$context}: file cleanup failed", [
                ...$logData,
                'exception' => $exception::class,
            ]);
        }
    }

    final public function safeDeleteFiles(array $paths, string $context, array $logData = []): void
    {
        foreach ($paths as $path) {
            $this->safeDeleteFile($path, $context, $logData);
        }
    }
}
