<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessPanorama implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

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

            $absolute = $disk->path($media->path);
            $size = $disk->size($media->path) ?: $media->size;
            $width = $media->width;
            $height = $media->height;

            if ((! $width || ! $height) && is_file($absolute)) {
                $dimensions = @getimagesize($absolute);
                if ($dimensions) {
                    [$width, $height] = $dimensions;
                }
            }

            $variants = is_array($media->variants) ? $media->variants : [];
            $variants['optimized'] = [
                'path' => $media->path,
                'width' => $width,
                'height' => $height,
                'validated' => true,
            ];

            $web = $this->writeJpegVariant($disk, $absolute, $media->path, 4096, 82, 'web');
            if ($web) {
                $variants['web'] = $web;
            }

            $thumb = $this->writeJpegVariant($disk, $absolute, $media->path, 640, 72, 'thumb');
            if ($thumb) {
                $variants['thumb'] = $thumb;
            }

            $media->update([
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'variants' => $variants,
            ]);
        } catch (Throwable $e) {
            Log::warning('ProcessPanorama failed', [
                'media_id' => $this->mediaId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  \Illuminate\Contracts\Filesystem\Filesystem  $disk
     * @return array{path: string, width: int, height: int, mime: string, size: int}|null
     */
    private function writeJpegVariant($disk, string $sourceAbsolute, string $sourcePath, int $maxWidth, int $quality, string $suffix): ?array
    {
        if (! function_exists('imagecreatefrompng') || ! is_file($sourceAbsolute)) {
            return null;
        }

        $mime = mime_content_type($sourceAbsolute) ?: '';
        $source = match (true) {
            str_contains($mime, 'png') || str_ends_with(strtolower($sourceAbsolute), '.png') => @imagecreatefrompng($sourceAbsolute),
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($sourceAbsolute),
            str_contains($mime, 'webp') && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($sourceAbsolute),
            default => null,
        };

        if (! $source) {
            return null;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW < 1 || $srcH < 1) {
            imagedestroy($source);

            return null;
        }

        $dstW = $srcW > $maxWidth ? $maxWidth : $srcW;
        $dstH = (int) max(1, round($srcH * ($dstW / $srcW)));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        if (! $canvas) {
            imagedestroy($source);

            return null;
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, false);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($source);

        $directory = trim(dirname($sourcePath), '.\\/');
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME).'-'.$suffix.'-'.Str::lower(Str::random(6)).'.jpg';
        $relative = ($directory !== '' ? $directory.'/' : '').$filename;
        $temp = tempnam(sys_get_temp_dir(), 'pano');

        imagejpeg($canvas, $temp, $quality);
        imagedestroy($canvas);

        $disk->put($relative, file_get_contents($temp) ?: '');
        @unlink($temp);

        return [
            'path' => $relative,
            'width' => $dstW,
            'height' => $dstH,
            'mime' => 'image/jpeg',
            'size' => $disk->size($relative) ?: 0,
        ];
    }
}
