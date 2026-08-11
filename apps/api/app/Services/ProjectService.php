<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function listPublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $filters['publication_status'] = 'published';

        return $this->filteredQuery($filters)
            ->with(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef'])
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug, bool $publishedOnly = false): Project
    {
        $query = Project::query()
            ->with([
                'projectType',
                'coverMedia',
                'categoryRef',
                'subcategoryRef',
                'projectMedia' => function ($q) use ($publishedOnly) {
                    if ($publishedOnly) {
                        $q->where('is_published', true);
                    }
                    $q->orderBy('sort_order')->with('media');
                },
                'galleryChanges' => function ($q) use ($publishedOnly) {
                    if ($publishedOnly) {
                        $q->featured();
                    }
                    $q->orderBy('sort_order')
                        ->with(['beforeMedia', 'designMedia', 'afterMedia']);
                },
                'virtualTour.scenes.panoramaMedia',
                'virtualTour.scenes.hotspots',
            ])
            ->where('slug', $slug);

        if ($publishedOnly) {
            $query->published();
        }

        /** @var Project $project */
        $project = $query->firstOrFail();

        if ($publishedOnly) {
            $project->increment('views_count');
        }

        return $project;
    }

    public function create(array $data, ?int $userId = null): Project
    {
        return DB::transaction(function () use ($data, $userId) {
            $mediaIds = Arr::pull($data, 'media', []);
            $data = $this->syncCategoryFromRelation($data);

            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);
            $data['created_by'] = $userId;
            $data['sort_order'] = $data['sort_order']
                ?? ((int) Project::query()->max('sort_order') + 1);

            /** @var Project $project */
            $project = Project::query()->create($data);

            $this->syncProjectMedia($project, $mediaIds);

            ActivityLog::query()->create([
                'user_id' => $userId,
                'action' => 'project.created',
                'description' => "Proyecto creado: {$project->title}",
                'subject_type' => Project::class,
                'subject_id' => $project->id,
            ]);

            return $project->load(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef', 'projectMedia.media']);
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $mediaIds = Arr::pull($data, 'media', null);
            $data = $this->syncCategoryFromRelation($data);

            if (isset($data['title']) && empty($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['title'], $project->id);
            } elseif (! empty($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['slug'], $project->id);
            }

            $project->update($data);

            if (is_array($mediaIds)) {
                $this->syncProjectMedia($project, $mediaIds);
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'project.updated',
                'description' => "Proyecto actualizado: {$project->title}",
                'subject_type' => Project::class,
                'subject_id' => $project->id,
            ]);

            return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef', 'projectMedia.media']);
        });
    }

    /**
     * When a `category_id` is provided, keep the legacy `category` string
     * column in sync (as the category's slug) for backward compatibility
     * with code that still filters/displays by that string.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function syncCategoryFromRelation(array $data): array
    {
        if (! array_key_exists('category_id', $data)) {
            return $data;
        }

        if (empty($data['category_id'])) {
            return $data;
        }

        $category = Category::query()->find($data['category_id']);

        if ($category) {
            $data['category'] = $category->slug;
        }

        return $data;
    }

    public function publish(Project $project): Project
    {
        $project->update([
            'publication_status' => 'published',
            'published_at' => $project->published_at ?? now(),
        ]);

        return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef']);
    }

    public function archive(Project $project): Project
    {
        $project->update([
            'publication_status' => 'archived',
        ]);

        return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef']);
    }

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Project::query()->where('id', $id)->update(['sort_order' => $index]);
            }
        });
    }

    public function move(Project $project, string $direction): Project
    {
        $ids = Project::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $index = array_search($project->id, $ids, true);
        if ($index === false) {
            return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef']) ?? $project;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($ids)) {
            return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef']) ?? $project;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        $this->reorder($ids);

        return $project->fresh(['projectType', 'coverMedia', 'categoryRef', 'subcategoryRef']) ?? $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $mediaItems
     */
    protected function syncProjectMedia(Project $project, array $mediaItems): void
    {
        if ($mediaItems === []) {
            return;
        }

        ProjectMedia::query()->where('project_id', $project->id)->delete();

        foreach ($mediaItems as $index => $item) {
            ProjectMedia::query()->create([
                'project_id' => $project->id,
                'media_id' => $item['media_id'],
                'type' => $item['type'] ?? 'gallery',
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
                'subcategory' => $item['subcategory'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
                'is_cover' => (bool) ($item['is_cover'] ?? false),
                'is_published' => array_key_exists('is_published', $item)
                    ? (bool) $item['is_published']
                    : true,
            ]);

            if (! empty($item['is_cover'])) {
                $project->update(['cover_media_id' => $item['media_id']]);
            }
        }
    }

    protected function filteredQuery(array $filters): Builder
    {
        return Project::query()
            ->when($filters['publication_status'] ?? null, fn (Builder $q, string $status) => $q->where('publication_status', $status))
            ->when($filters['category'] ?? null, fn (Builder $q, string $category) => $q->where('category', $category))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['project_type_id'] ?? null, fn (Builder $q, int $id) => $q->where('project_type_id', $id))
            ->when(isset($filters['is_featured']), fn (Builder $q) => $q->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            });
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $counter = 1;

        while (
            Project::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
