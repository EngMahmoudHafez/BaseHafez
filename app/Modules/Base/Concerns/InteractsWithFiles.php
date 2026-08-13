<?php

namespace App\Modules\Base\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

/**
 * The single file-handling concern for the whole base: generic file/image
 * storage for Services and Repositories, plus optional model-image sugar
 * (`putImage`, `image_url`) for Eloquent models. There is exactly one
 * `optimizeImage()` in the codebase — it lives here.
 *
 * Generic use (Service / Repository):
 *
 *     $path = $this->storeImage($request->file('avatar'), 'users/avatar', maxDimension: 1600, quality: 82);
 *     $this->safeDeleteFiles($oldPaths, 'context');
 *
 * Model use — add the trait and (optionally) name the column; defaults to `avatar`:
 *
 *     class User extends Authenticatable { use InteractsWithFiles; }
 *     $user->putImage($file);   // store (optimized), persist, delete the old file
 *     $user->image_url;         // public URL or null
 *     $user->deleteImage();
 *
 * The `@mixin` lets the model-image helpers resolve Eloquent methods; it is
 * inert on the Services/Repositories that only call the generic helpers.
 *
 * @mixin Model
 */
trait InteractsWithFiles
{
    // ---------------------------------------------------------------------
    // Generic file operations
    // ---------------------------------------------------------------------

    /**
     * Store an uploaded file untouched on the public disk and return its path.
     */
    public function storeFile(UploadedFile $file, string $folder): string
    {
        $path = Storage::disk('public')->putFile(trim($folder, '/'), $file);

        if (! is_string($path)) {
            throw new RuntimeException('The uploaded file could not be stored.');
        }

        return $path;
    }

    /**
     * Store a new file and delete the one it replaces (only after the new one
     * is written), returning the new path.
     */
    public function replaceFile(UploadedFile $file, string $folder, ?string $previous = null): string
    {
        $path = $this->storeFile($file, $folder);

        if (filled($previous) && $previous !== $path) {
            $this->deleteFile($previous);
        }

        return $path;
    }

    public function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @param  iterable<string|null>  $paths
     */
    public function deleteFiles(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteFile($path);
        }
    }

    /**
     * Delete a file, swallowing and logging any failure so a cleanup problem
     * never aborts the surrounding workflow.
     *
     * @param  array<string, mixed>  $logData
     */
    public function safeDeleteFile(?string $path, string $context, array $logData = []): void
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

    /**
     * @param  array<int, string>  $paths
     * @param  array<string, mixed>  $logData
     */
    public function safeDeleteFiles(array $paths, string $context, array $logData = []): void
    {
        foreach ($paths as $path) {
            $this->safeDeleteFile($path, $context, $logData);
        }
    }

    public function fileUrl(?string $path): ?string
    {
        return blank($path) ? null : asset('storage/' . ltrim((string) $path, '/'));
    }

    // ---------------------------------------------------------------------
    // Image operations
    // ---------------------------------------------------------------------

    /**
     * Store an uploaded image, downscaling and re-encoding it when possible and
     * transparently falling back to the original file otherwise. Returns the path.
     */
    public function storeImage(UploadedFile $image, string $folder, int $maxDimension = 1600, int $quality = 82): string
    {
        $optimized = $this->optimizeImage($image, $maxDimension, $quality);

        if ($optimized !== null) {
            $path = trim($folder, '/') . '/' . $image->hashName();
            Storage::disk('public')->put($path, $optimized);

            return $path;
        }

        return $this->storeFile($image, $folder);
    }

    /**
     * Store a new image and delete the one it replaces, returning the new path.
     */
    public function replaceImage(UploadedFile $image, string $folder, ?string $previous = null, int $maxDimension = 1600, int $quality = 82): string
    {
        $path = $this->storeImage($image, $folder, $maxDimension, $quality);

        if (filled($previous) && $previous !== $path) {
            $this->deleteFile($previous);
        }

        return $path;
    }

    public function deleteImageFile(?string $path): void
    {
        $this->deleteFile($path);
    }

    /**
     * Downscale oversized raster images and re-encode them with sane quality.
     * Returns null when the file is not a processable image or GD is unavailable,
     * so callers transparently fall back to storing the original file untouched.
     */
    protected function optimizeImage(UploadedFile $image, int $maxDimension = 1600, int $quality = 82): ?string
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
                quality: $quality,
            );
        } catch (Throwable) {
            return null;
        }
    }

    // ---------------------------------------------------------------------
    // Model image support (used when the trait is on an Eloquent model)
    // ---------------------------------------------------------------------

    protected function imageColumn(): string
    {
        return 'avatar';
    }

    protected function imageFolder(): string
    {
        return Str::of(class_basename($this))->snake()->plural() . '/' . $this->imageColumn();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->fileUrl($this->getAttribute($this->imageColumn()));
    }

    /**
     * Store the uploaded image, point the column at it, persist, and delete the
     * file it replaced. Returns the stored path.
     */
    public function putImage(UploadedFile $file): string
    {
        $previous = $this->getAttribute($this->imageColumn());
        $path = $this->storeImage($file, $this->imageFolder());

        try {
            $this->forceFill([$this->imageColumn() => $path])->save();
        } catch (Throwable $exception) {
            // The row was not updated — do not leave the just-written file orphaned.
            $this->deleteFile($path);

            throw $exception;
        }

        if (filled($previous) && $previous !== $path) {
            $this->deleteFile((string) $previous);
        }

        return $path;
    }

    public function deleteImage(): void
    {
        $path = $this->getAttribute($this->imageColumn());

        if (blank($path)) {
            return;
        }

        // Clear the column first, then remove the file: a storage failure leaves
        // an orphaned file (harmless) rather than a row pointing at a missing one.
        $this->forceFill([$this->imageColumn() => null])->save();

        $this->deleteFile((string) $path);
    }
}
