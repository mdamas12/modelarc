<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWeAreRequest;
use App\Http\Resources\WeAreResource;
use App\Http\Resources\WeAreTeamResource;
use App\Models\WeAre;
use App\Models\WeAreTeam;
use Illuminate\Http\JsonResponse;

class WeAreController extends Controller
{
    public function show(): JsonResponse
    {
        $weAre = WeAre::singleton();
        $teams = WeAreTeam::query()->ordered()->get();

        return response()->json([
            'data' => [
                'we_are' => (new WeAreResource($weAre))->resolve(),
                'teams' => WeAreTeamResource::collection($teams)->resolve(),
            ],
        ]);
    }

    public function update(UpdateWeAreRequest $request): WeAreResource
    {
        $weAre = WeAre::singleton();
        $weAre->update($request->validated());

        return new WeAreResource($weAre->fresh());
    }
}
