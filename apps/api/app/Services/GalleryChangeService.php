<?php

namespace App\Services;

use App\Models\GalleryChange;
use App\Models\Project;
use App\Support\MediaTaxonomy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GalleryChangeService
{
    public function listForProject(Project $project): Collection
    {
        return $project->galleryChanges()
            ->with(['beforeMedia', 'designMedia', 'afterMedia'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function create(Project $project, array $data): GalleryChange
    {
        $payload = $this->normalizePayload($project, $data);
        $payload['project_id'] = $project->id;
        $payload['sort_order'] = $payload['sort_order']
            ?? ((int) $project->galleryChanges()->max('sort_order') + 1);

        /** @var GalleryChange $change */
        $change = GalleryChange::query()->create($payload);

        return $change->load(['beforeMedia', 'designMedia', 'afterMedia']);
    }

    public function update(GalleryChange $change, array $data): GalleryChange
    {
        $payload = $this->normalizePayload($change->project, $data, $change);
        $change->update($payload);

        return $change->fresh()->load(['beforeMedia', 'designMedia', 'afterMedia']);
    }

    public function delete(GalleryChange $change): void
    {
        $change->delete();
    }

    /**
     * @param  list<int>  $ids
     */
    public function reorder(Project $project, array $ids): void
    {
        DB::transaction(function () use ($project, $ids) {
            foreach ($ids as $index => $id) {
                GalleryChange::query()
                    ->where('project_id', $project->id)
                    ->where('id', $id)
                    ->update(['sort_order' => $index]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePayload(Project $project, array $data, ?GalleryChange $existing = null): array
    {
        $beforeId = $data['before_media_id'] ?? $existing?->before_media_id;
        $designId = array_key_exists('design_media_id', $data)
            ? $data['design_media_id']
            : $existing?->design_media_id;
        $afterId = array_key_exists('after_media_id', $data)
            ? $data['after_media_id']
            : $existing?->after_media_id;

        if (! $beforeId) {
            throw ValidationException::withMessages([
                'before_media_id' => ['La imagen de antes es obligatoria.'],
            ]);
        }

        if (! $designId && ! $afterId) {
            throw ValidationException::withMessages([
                'design_media_id' => ['Debes agregar al menos una imagen de diseño o de después.'],
                'after_media_id' => ['Debes agregar al menos una imagen de diseño o de después.'],
            ]);
        }

        $compareWith = $data['compare_with'] ?? $existing?->compare_with;

        if ($designId && ! $afterId) {
            $compareWith = 'design';
        } elseif ($afterId && ! $designId) {
            $compareWith = 'after';
        } elseif (! in_array($compareWith, ['design', 'after'], true)) {
            throw ValidationException::withMessages([
                'compare_with' => ['Selecciona si el slider usa Diseño o Después.'],
            ]);
        }

        if ($compareWith === 'design' && ! $designId) {
            throw ValidationException::withMessages([
                'compare_with' => ['No hay imagen de diseño para comparar.'],
            ]);
        }

        if ($compareWith === 'after' && ! $afterId) {
            throw ValidationException::withMessages([
                'compare_with' => ['No hay imagen de después para comparar.'],
            ]);
        }

        $subcategory = $data['subcategory'] ?? $existing?->subcategory;
        if ($subcategory !== null && $subcategory !== ''
            && ! MediaTaxonomy::isValidSubcategory($project->category, $subcategory)) {
            throw ValidationException::withMessages([
                'subcategory' => ['La subcategoría no es válida para la categoría del proyecto.'],
            ]);
        }

        return [
            'before_media_id' => $beforeId,
            'design_media_id' => $designId,
            'after_media_id' => $afterId,
            'compare_with' => $compareWith,
            'subcategory' => $subcategory ?: null,
            'title' => array_key_exists('title', $data) ? ($data['title'] ?: null) : $existing?->title,
            'description' => array_key_exists('description', $data)
                ? ($data['description'] ?: null)
                : $existing?->description,
            'sort_order' => array_key_exists('sort_order', $data)
                ? (int) $data['sort_order']
                : $existing?->sort_order,
            'is_featured' => array_key_exists('is_featured', $data)
                ? (bool) $data['is_featured']
                : ($existing?->is_featured ?? false),
        ];
    }
}
