<?php

namespace App\Services;

use App\Models\WeAreTeam;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WeAreTeamImageService
{
    public const FOLDER = 'team_images';

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

    public function nextOrder(): int
    {
        return ((int) WeAreTeam::query()->max('order')) + 1;
    }

    private function ensureDirectory(): void
    {
        Storage::disk('public')->makeDirectory(self::FOLDER);
    }
}
