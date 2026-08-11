<?php

namespace App\Services;

use App\Models\HeroGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroImageService
{
    public const FOLDER = 'hero_images';

    public const MAX_PUBLISHED = 4;

    public function store(UploadedFile $file): string
    {
        $this->ensureDirectory();

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs(self::FOLDER, $filename, 'public');
    }

    public function replace(?string $currentPath, UploadedFile $file): string
    {
        $newPath = $this->store($file);
        $this->delete($currentPath);

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function nextOrder(int $heroId): int
    {
        return ((int) HeroGallery::query()->where('hero_id', $heroId)->max('order')) + 1;
    }

    public function publishedCount(int $heroId, ?int $exceptId = null): int
    {
        return HeroGallery::query()
            ->where('hero_id', $heroId)
            ->published()
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->count();
    }

    private function ensureDirectory(): void
    {
        Storage::disk('public')->makeDirectory(self::FOLDER);
    }
}
