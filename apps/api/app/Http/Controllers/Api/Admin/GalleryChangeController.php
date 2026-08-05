<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderGalleryChangesRequest;
use App\Http\Requests\Admin\StoreGalleryChangeRequest;
use App\Http\Requests\Admin\UpdateGalleryChangeRequest;
use App\Http\Resources\GalleryChangeResource;
use App\Models\GalleryChange;
use App\Models\Project;
use App\Services\GalleryChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GalleryChangeController extends Controller
{
    public function __construct(private readonly GalleryChangeService $galleryChanges) {}

    public function index(Project $project): AnonymousResourceCollection
    {
        return GalleryChangeResource::collection($this->galleryChanges->listForProject($project));
    }

    public function store(StoreGalleryChangeRequest $request, Project $project): JsonResponse
    {
        $change = $this->galleryChanges->create($project, $request->validated());

        return (new GalleryChangeResource($change))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateGalleryChangeRequest $request,
        Project $project,
        GalleryChange $galleryChange,
    ): GalleryChangeResource {
        $this->assertBelongsToProject($project, $galleryChange);

        return new GalleryChangeResource(
            $this->galleryChanges->update($galleryChange, $request->validated())
        );
    }

    public function destroy(Project $project, GalleryChange $galleryChange): JsonResponse
    {
        $this->assertBelongsToProject($project, $galleryChange);
        $this->galleryChanges->delete($galleryChange);

        return response()->json([
            'data' => ['message' => 'Comparación eliminada.'],
        ]);
    }

    public function reorder(ReorderGalleryChangesRequest $request, Project $project): JsonResponse
    {
        $this->galleryChanges->reorder($project, $request->validated('ids'));

        return response()->json([
            'data' => ['message' => 'Orden actualizado.'],
        ]);
    }

    private function assertBelongsToProject(Project $project, GalleryChange $galleryChange): void
    {
        abort_unless($galleryChange->project_id === $project->id, 404);
    }
}
