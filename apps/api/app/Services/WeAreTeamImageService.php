<?php

namespace App\Services;

use App\Models\WeAreTeam;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class WeAreTeamImageService
{
    public const FOLDER = 'team_images';

    public const DISPLAY_MAX_WIDTH = 2560;

    public function store(UploadedFile $file): array
    {
        $this->ensureDirectory();

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs(self::FOLDER, $filename, 'public');
        $displayPath = $this->makeDisplayVariant($path);

        return [
            'path' => $path,
            'display_path' => $displayPath,
        ];
    }

    public function replace(?string $currentPath, ?string $currentDisplayPath, UploadedFile $file): array
    {
        $stored = $this->store($file);
        $this->delete($currentPath);
        $this->delete($currentDisplayPath);

        return $stored;
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

    public function ensureDisplayVariant(WeAreTeam $team): ?string
    {
        if ($team->display_path && Storage::disk('public')->exists($team->display_path)) {
            return $team->display_path;
        }

        if (! $team->path || ! Storage::disk('public')->exists($team->path)) {
            return null;
        }

        $displayPath = $this->makeDisplayVariant($team->path);
        if ($displayPath) {
            $team->update(['display_path' => $displayPath]);
        }

        return $displayPath;
    }

    private function makeDisplayVariant(string $originalPath): ?string
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            $contents = $disk->get($originalPath);
            if ($contents === null) {
                return null;
            }

            $resized = Image::read($contents)->scaleDown(width: self::DISPLAY_MAX_WIDTH);
            $filename = pathinfo($originalPath, PATHINFO_FILENAME).'_display.webp';
            $displayPath = self::FOLDER.'/'.$filename;
            $disk->put($displayPath, (string) $resized->toWebp(90));

            return $displayPath;
        } catch (Throwable $e) {
            Log::warning('WeAreTeam display variant failed', [
                'path' => $originalPath,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function ensureDirectory(): void
    {
        Storage::disk('public')->makeDirectory(self::FOLDER);
    }
}
