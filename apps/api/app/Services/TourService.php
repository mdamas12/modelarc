<?php

namespace App\Services;

use App\Models\TourScene;
use App\Models\VirtualTour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TourService
{
    public function findPublicBySlug(string $slug): VirtualTour
    {
        return VirtualTour::query()
            ->published()
            ->with([
                'project:id,title,slug',
                'initialScene',
                'scenes.panoramaMedia',
                'scenes.thumbnailMedia',
                'scenes.hotspots.media',
                'scenes.hotspots.targetScene',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data): VirtualTour
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);

        return VirtualTour::query()->create($data)->load('project');
    }

    public function update(VirtualTour $tour, array $data): VirtualTour
    {
        if (! empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name'], $tour->id);
        } elseif (! empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $tour->id);
        }

        $tour->update($data);

        return $tour->fresh(['project', 'scenes', 'initialScene']);
    }

    public function publish(VirtualTour $tour): VirtualTour
    {
        $tour->update([
            'status' => 'published',
            'published_at' => $tour->published_at ?? now(),
        ]);

        $tour->project?->update(['has_virtual_tour' => true]);

        return $tour->fresh(['scenes', 'initialScene']);
    }

    public function delete(VirtualTour $tour): void
    {
        DB::transaction(function () use ($tour) {
            $project = $tour->project;
            $tour->delete();

            if ($project && $project->virtualTours()->count() === 0) {
                $project->update(['has_virtual_tour' => false]);
            }
        });
    }

    public function createScene(VirtualTour $tour, array $data): TourScene
    {
        $data['virtual_tour_id'] = $tour->id;
        $data['slug'] = $this->uniqueSceneSlug($tour, $data['slug'] ?? $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? ($tour->scenes()->max('sort_order') + 1);

        /** @var TourScene $scene */
        $scene = TourScene::query()->create($data);

        if (! $tour->initial_scene_id) {
            $tour->update(['initial_scene_id' => $scene->id]);
        }

        return $scene->load(['panoramaMedia', 'thumbnailMedia', 'hotspots']);
    }

    public function updateScene(TourScene $scene, array $data): TourScene
    {
        if (! empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSceneSlug($scene->virtualTour, $data['name'], $scene->id);
        } elseif (! empty($data['slug'])) {
            $data['slug'] = $this->uniqueSceneSlug($scene->virtualTour, $data['slug'], $scene->id);
        }

        $scene->update($data);

        return $scene->fresh(['panoramaMedia', 'thumbnailMedia', 'hotspots']);
    }

    public function deleteScene(TourScene $scene): void
    {
        $tour = $scene->virtualTour;

        if ($tour && $tour->initial_scene_id === $scene->id) {
            $tour->update(['initial_scene_id' => null]);
        }

        $scene->delete();
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $counter = 1;

        while (
            VirtualTour::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function uniqueSceneSlug(VirtualTour $tour, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $counter = 1;

        while (
            TourScene::query()
                ->where('virtual_tour_id', $tour->id)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
