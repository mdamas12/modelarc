<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderWeAreTeamRequest;
use App\Http\Requests\Admin\StoreWeAreTeamRequest;
use App\Http\Requests\Admin\UpdateWeAreTeamRequest;
use App\Http\Resources\WeAreTeamResource;
use App\Models\WeAreTeam;
use App\Services\WeAreTeamImageService;
use Illuminate\Http\JsonResponse;

class WeAreTeamController extends Controller
{
    public function __construct(
        private readonly WeAreTeamImageService $images,
    ) {}

    public function store(StoreWeAreTeamRequest $request): JsonResponse
    {
        $data = $request->validated();
        $stored = $this->images->store($request->file('image'));

        $team = WeAreTeam::query()->create([
            'path' => $stored['path'],
            'display_path' => $stored['display_path'],
            'title' => $data['title'] ?? null,
            'order' => $data['order'] ?? $this->images->nextOrder(),
            'published' => array_key_exists('published', $data) ? (bool) $data['published'] : true,
        ]);

        return (new WeAreTeamResource($team))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateWeAreTeamRequest $request, WeAreTeam $weAreTeam): WeAreTeamResource
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $stored = $this->images->replace(
                $weAreTeam->path,
                $weAreTeam->display_path,
                $request->file('image'),
            );
            $data['path'] = $stored['path'];
            $data['display_path'] = $stored['display_path'];
        }

        unset($data['image']);

        $weAreTeam->update($data);

        return new WeAreTeamResource($weAreTeam->fresh());
    }

    public function reorder(ReorderWeAreTeamRequest $request): JsonResponse
    {
        foreach ($request->validated('ids') as $index => $id) {
            WeAreTeam::query()->whereKey($id)->update(['order' => $index + 1]);
        }

        return response()->json([
            'data' => ['message' => 'Orden actualizado.'],
        ]);
    }

    public function destroy(WeAreTeam $weAreTeam): JsonResponse
    {
        $this->images->delete($weAreTeam->display_path);
        $this->images->delete($weAreTeam->path);
        $weAreTeam->delete();

        return response()->json([
            'data' => ['message' => 'Imagen del equipo eliminada.'],
        ]);
    }
}
