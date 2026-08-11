<?php

namespace App\Services;

use App\Models\WeAreTeam;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
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
            $absolute = $disk->path($originalPath);
            if (! is_file($absolute)) {
                return null;
            }

            // Prefer GD native resize for large originals (avoids facade conflicts / memory spikes).
            $info = @getimagesize($absolute);
            if (! $info) {
                return null;
            }

            [$width, $height] = $info;
            $mime = $info['mime'] ?? '';
            $src = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($absolute),
                'image/png' => @imagecreatefrompng($absolute),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : false,
                'image/gif' => @imagecreatefromgif($absolute),
                default => false,
            };

            if ($src === false) {
                $manager = new ImageManager(new Driver());
                $resized = $manager->read($absolute)->scaleDown(width: self::DISPLAY_MAX_WIDTH);
                $filename = pathinfo($originalPath, PATHINFO_FILENAME).'_display.jpg';
                $displayPath = self::FOLDER.'/'.$filename;
                $disk->put($displayPath, (string) $resized->toJpeg(90));

                return $displayPath;
            }

            $targetWidth = min(self::DISPLAY_MAX_WIDTH, (int) $width);
            $targetHeight = (int) round($height * ($targetWidth / max(1, $width)));
            $dst = imagecreatetruecolor($targetWidth, $targetHeight);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, (int) $width, (int) $height);

            ob_start();
            imagejpeg($dst, null, 90);
            $encoded = ob_get_clean();
            imagedestroy($src);
            imagedestroy($dst);

            if ($encoded === false || $encoded === '') {
                return null;
            }

            $filename = pathinfo($originalPath, PATHINFO_FILENAME).'_display.jpg';
            $displayPath = self::FOLDER.'/'.$filename;
            $disk->put($displayPath, $encoded);

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
