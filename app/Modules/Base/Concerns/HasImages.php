<?php

namespace App\Modules\Base\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

/**
 * Generic, self-contained image handling for any Eloquent model. Add the trait
 * and — as long as the image column is named `avatar` — nothing else is needed:
 *
 *     class Product extends Model
 *     {
 *         use HasImages;
 *     }
 *
 * If the column has a different name, override a single method:
 *
 *     class Post extends Model
 *     {
 *         use HasImages;
 *
 *         protected function imageColumn(): string
 *         {
 *             return 'cover';
 *         }
 *     }
 *
 *     $model->putImage($file);   // store, persist, delete the previous file
 *     $model->image_url;         // public URL or null
 *     $model->deleteImage();     // remove the file and clear the column
 *
 * Files land on the `public` disk under "{plural model}/{column}" (e.g.
 * posts/cover). Override `imageFolder()` for a custom path, or
 * `shouldOptimizeImages()` to enable intervention/image resizing.
 */
trait HasImages
{
    protected function imageColumn(): string
    {
        return 'avatar';
    }

    protected function imageFolder(): string
    {
        return Str::of(class_basename($this))->snake()->plural() . '/' . $this->imageColumn();
    }

    protected function shouldOptimizeImages(): bool
    {
        return false;
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->getAttribute($this->imageColumn());

        return blank($path) ? null : asset('storage/' . ltrim((string) $path, '/'));
    }

    /**
     * Store the uploaded image, point the column at it, persist, and delete the
     * file it replaced. Returns the stored path.
     */
    public function putImage(UploadedFile $file): string
    {
        $previous = $this->getAttribute($this->imageColumn());
        $path = $this->writeImage($file);

        try {
            $this->forceFill([$this->imageColumn() => $path])->save();
        } catch (Throwable $exception) {
            // The row was not updated — do not leave the just-written file orphaned.
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if (filled($previous) && $previous !== $path) {
            Storage::disk('public')->delete($previous);
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

        Storage::disk('public')->delete($path);
    }

    private function writeImage(UploadedFile $file): string
    {
        if ($this->shouldOptimizeImages() && ($optimized = $this->optimizeImage($file)) !== null) {
            $path = $this->imageFolder() . '/' . $file->hashName();
            Storage::disk('public')->put($path, $optimized);

            return $path;
        }

        $path = Storage::disk('public')->putFile($this->imageFolder(), $file);

        if (! is_string($path)) {
            throw new RuntimeException('The image could not be stored.');
        }

        return $path;
    }

    private function optimizeImage(UploadedFile $file, int $maxDimension = 1600): ?string
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return null;
        }

        try {
            $image = ImageManager::gd()->read($file->getRealPath());

            if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
                $image->scaleDown($maxDimension, $maxDimension);
            }

            return (string) $image->encodeByExtension($file->getClientOriginalExtension() ?: 'jpg', quality: 82);
        } catch (Throwable) {
            return null;
        }
    }
}
