<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeAreResource;
use App\Http\Resources\WeAreTeamResource;
use App\Models\WeAre;
use App\Models\WeAreTeam;
use Illuminate\Http\JsonResponse;

class WeAreController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $weAre = WeAre::singleton();
        $teams = WeAreTeam::query()->published()->ordered()->get();

        return response()->json([
            'data' => [
                'we_are' => (new WeAreResource($weAre))->resolve(),
                'teams' => WeAreTeamResource::collection($teams)->resolve(),
            ],
        ]);
    }
}
