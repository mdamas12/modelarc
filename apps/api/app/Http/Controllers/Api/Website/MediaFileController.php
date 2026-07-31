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
        abort_unless(
            $media->path && Storage::disk($media->disk)->exists($media->path),
            404
        );

        return Storage::disk($media->disk)->response(
            $media->path,
            $media->original_name,
            [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]
        );
    }
}
