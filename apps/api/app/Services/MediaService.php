<?php

namespace App\Services;

use App\Jobs\ProcessPanorama;
use App\Jobs\ProcessProjectImage;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(
        UploadedFile $file,
        string $type = 'image',
        ?int $userId = null,
        ?string $folder = null,
        array $meta = [],
    ): Media {
        $disk = $this->resolveDisk();
        $folder = $folder ?? match ($type) {
            'panorama' => 'panoramas',
            'video' => 'videos',
            'document' => 'documents',
            default => 'images',
        };

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($folder, $filename, $disk);

        $width = null;
        $height = null;

        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $dimensions = @getimagesize($file->getRealPath());
            if ($dimensions) {
                [$width, $height] = $dimensions;
            }
        }

        $nextOrder = ((int) Media::query()->max('sort_order')) + 1;

        /** @var Media $media */
        $media = Media::query()->create([
            'uuid' => (string) Str::uuid(),
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'variants' => null,
            'type' => $type,
            'category' => $meta['category'] ?? null,
            'subcategory' => $meta['subcategory'] ?? null,
            'sort_order' => $meta['sort_order'] ?? $nextOrder,
            'is_published' => array_key_exists('is_published', $meta)
                ? (bool) $meta['is_published']
                : true,
            'created_by' => $userId,
        ]);

        if ($type === 'panorama') {
            ProcessPanorama::dispatch($media->id);
        } elseif ($type === 'image') {
            ProcessProjectImage::dispatch($media->id);
        }

        return $media->fresh();
    }

    public function update(Media $media, array $data): Media
    {
        $media->update($data);

        return $media->fresh();
    }

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void
    {
        foreach ($ids as $index => $id) {
            Media::query()->whereKey($id)->update(['sort_order' => $index]);
        }
    }

    public function destroy(Media $media): void
    {
        if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $variants = $media->variants ?? [];
        foreach ($variants as $variant) {
            $variantPath = is_array($variant) ? ($variant['path'] ?? null) : $variant;
            if ($variantPath && Storage::disk($media->disk)->exists($variantPath)) {
                Storage::disk($media->disk)->delete($variantPath);
            }
        }

        $media->delete();
    }

    public function resolveDisk(): string
    {
        $spacesKey = config('filesystems.disks.spaces.key');
        $spacesBucket = config('filesystems.disks.spaces.bucket');

        if ($spacesKey && $spacesBucket) {
            return 'spaces';
        }

        return 'public';
    }
}
