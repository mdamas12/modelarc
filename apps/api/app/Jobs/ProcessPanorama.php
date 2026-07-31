<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPanorama implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if (! $media || $media->type !== 'panorama') {
            return;
        }

        try {
            $disk = Storage::disk($media->disk);

            if (! $disk->exists($media->path)) {
                Log::warning('ProcessPanorama: file missing', ['media_id' => $media->id]);

                return;
            }

            $size = $disk->size($media->path);
            $width = $media->width;
            $height = $media->height;

            if ((! $width || ! $height) && str_starts_with($media->mime_type, 'image/')) {
                $temp = tempnam(sys_get_temp_dir(), 'pano');
                file_put_contents($temp, $disk->get($media->path));
                $dimensions = @getimagesize($temp);
                @unlink($temp);

                if ($dimensions) {
                    [$width, $height] = $dimensions;
                }
            }

            $media->update([
                'size' => $size ?: $media->size,
                'width' => $width,
                'height' => $height,
                'variants' => [
                    'optimized' => [
                        'path' => $media->path,
                        'width' => $width,
                        'height' => $height,
                        'validated' => true,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('ProcessPanorama failed', [
                'media_id' => $this->mediaId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
