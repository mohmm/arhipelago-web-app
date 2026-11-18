<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Stores photos travellers upload: turned upright, resized and re-encoded as
 * JPEG. Re-encoding also drops the EXIF block, so a photo never publishes
 * where its owner lives.
 */
class ImageStore
{
    /** JPEG quality: visually lossless for photos, a fraction of a phone's file size. */
    private const QUALITY = 82;

    /**
     * Keep the whole picture, at most this many pixels on its longest side.
     * Returns the path on the public disk.
     */
    public function storeScaled(UploadedFile $file, string $directory, int $maxSide): string
    {
        $image = Image::read($file->getRealPath())->scaleDown($maxSide, $maxSide);

        return $this->put($directory, (string) $image->toJpeg(self::QUALITY));
    }

    /**
     * Crop to a centred square of this size, for avatars.
     * Returns the path on the public disk.
     */
    public function storeSquare(UploadedFile $file, string $directory, int $side): string
    {
        $image = Image::read($file->getRealPath())->cover($side, $side);

        return $this->put($directory, (string) $image->toJpeg(self::QUALITY));
    }

    public function delete(?string $path): void
    {
        if ($path != null) {
            Storage::disk('public')->delete($path);
        }
    }

    private function put(string $directory, string $contents): string
    {
        $path = $directory.'/'.Str::uuid().'.jpg';

        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
