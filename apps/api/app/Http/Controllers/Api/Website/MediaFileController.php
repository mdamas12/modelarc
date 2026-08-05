<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController
{
    public function __invoke(Media $media): BinaryFileResponse|StreamedResponse
    {
        $variant = request()->query('variant', 'web');
        if (! in_array($variant, ['web', 'thumb', 'original'], true)) {
            $variant = 'web';
        }

        [$path, $mime, $downloadName] = $this->resolveVariant($media, $variant);

        abort_unless(
            $path && Storage::disk($media->disk)->exists($path),
            404
        );

        return Storage::disk($media->disk)->response(
            $path,
            $downloadName,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]
        );
    }

    /**
     * @return array{0: string|null, 1: string, 2: string}
     */
    private function resolveVariant(Media $media, string $variant): array
    {
        $variants = is_array($media->variants) ? $media->variants : [];
        $originalName = $media->original_name ?: basename((string) $media->path);

        if ($variant === 'original') {
            return [$media->path, $media->mime_type ?: 'application/octet-stream', $originalName];
        }

        if ($variant === 'thumb' && ! empty($variants['thumb']['path'])) {
            return [
                (string) $variants['thumb']['path'],
                (string) ($variants['thumb']['mime'] ?? 'image/jpeg'),
                pathinfo($originalName, PATHINFO_FILENAME).'-thumb.jpg',
            ];
        }

        if (! empty($variants['web']['path'])) {
            return [
                (string) $variants['web']['path'],
                (string) ($variants['web']['mime'] ?? 'image/jpeg'),
                pathinfo($originalName, PATHINFO_FILENAME).'-web.jpg',
            ];
        }

        return [$media->path, $media->mime_type ?: 'application/octet-stream', $originalName];
    }
}
