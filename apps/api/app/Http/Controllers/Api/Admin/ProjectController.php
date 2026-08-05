<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveProjectRequest;
use App\Http\Requests\Admin\ReorderProjectsRequest;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->projects->list($request->only([
            'publication_status',
            'category',
            'status',
            'project_type_id',
            'is_featured',
            'search',
        ]), (int) $request->integer('per_page', 15));

        return ProjectResource::collection($paginator);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projects->create($request->validated(), $request->user()?->id);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $project->load([
            'projectType',
            'coverMedia',
            'projectMedia.media',
            'galleryChanges.beforeMedia',
            'galleryChanges.designMedia',
            'galleryChanges.afterMedia',
            'virtualTour.scenes',
        ]);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        return new ProjectResource($this->projects->update($project, $request->validated()));
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->projects->delete($project);

        return response()->json([
            'data' => ['message' => 'Proyecto eliminado.'],
        ]);
    }

    public function publish(Project $project): ProjectResource
    {
        return new ProjectResource($this->projects->publish($project));
    }

    public function archive(Project $project): ProjectResource
    {
        return new ProjectResource($this->projects->archive($project));
    }

    public function reorder(ReorderProjectsRequest $request): JsonResponse
    {
        $this->projects->reorder($request->validated('ids'));

        return response()->json([
            'data' => ['message' => 'Orden de proyectos actualizado.'],
        ]);
    }

    public function move(MoveProjectRequest $request, Project $project): ProjectResource
    {
        return new ProjectResource(
            $this->projects->move($project, $request->validated('direction'))
        );
    }
}
