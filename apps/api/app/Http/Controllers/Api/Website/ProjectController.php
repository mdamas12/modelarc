<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\VirtualTourResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->projects->listPublished($request->only([
            'category',
            'status',
            'project_type_id',
            'is_featured',
            'search',
        ]), (int) $request->integer('per_page', 12));

        return ProjectResource::collection($paginator);
    }

    public function show(string $slug): ProjectResource
    {
        return new ProjectResource($this->projects->findBySlug($slug, true));
    }

    public function tour(string $slug): VirtualTourResource
    {
        $project = $this->projects->findBySlug($slug, true);
        $tour = $project->virtualTour()
            ->published()
            ->with([
                'initialScene',
                'scenes.panoramaMedia',
                'scenes.thumbnailMedia',
                'scenes.hotspots.media',
                'scenes.hotspots.targetScene',
            ])
            ->firstOrFail();

        return new VirtualTourResource($tour);
    }
}
