<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class ProcessProjectImage implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if (! $media || $media->type !== 'image') {
            return;
        }

        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $media->update([
                'variants' => [
                    'original' => [
                        'path' => $media->path,
                        'width' => $media->width,
                        'height' => $media->height,
                    ],
                ],
            ]);

            return;
        }

        try {
            $disk = Storage::disk($media->disk);
            $contents = $disk->get($media->path);

            if ($contents === null) {
                return;
            }

            $image = Image::read($contents);
            $variants = [
                'original' => [
                    'path' => $media->path,
                    'width' => $image->width(),
                    'height' => $image->height(),
                ],
            ];

            foreach ([
                'thumb' => 400,
                'medium' => 1200,
            ] as $name => $width) {
                $resized = Image::read($contents)->scaleDown(width: $width);
                $variantPath = $this->variantPath($media->path, $name, 'webp');
                $encoded = $resized->toWebp(80);
                $disk->put($variantPath, (string) $encoded);

                $variants[$name] = [
                    'path' => $variantPath,
                    'width' => $resized->width(),
                    'height' => $resized->height(),
                    'format' => 'webp',
                ];
            }

            $media->update([
                'width' => $image->width(),
                'height' => $image->height(),
                'variants' => $variants,
            ]);
        } catch (Throwable $e) {
            Log::warning('ProcessProjectImage failed', [
                'media_id' => $this->mediaId,
                'message' => $e->getMessage(),
            ]);

            $media->update([
                'variants' => [
                    'original' => [
                        'path' => $media->path,
                        'width' => $media->width,
                        'height' => $media->height,
                    ],
                ],
            ]);
        }
    }

    protected function variantPath(string $originalPath, string $name, string $extension): string
    {
        $directory = Str::beforeLast($originalPath, '/');
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);

        return ($directory ? "{$directory}/" : '')."{$filename}_{$name}.{$extension}";
    }
}
