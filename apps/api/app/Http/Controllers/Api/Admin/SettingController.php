<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertSettingRequest;
use App\Http\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SettingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SiteSettingResource::collection(SiteSetting::query()->orderBy('key')->get());
    }

    public function upsert(UpsertSettingRequest $request): JsonResponse
    {
        $setting = SiteSetting::query()->updateOrCreate(
            ['key' => $request->string('key')->toString()],
            ['value' => $request->input('value')],
        );

        return (new SiteSettingResource($setting))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(SiteSetting $setting): JsonResponse
    {
        $setting->delete();

        return response()->json([
            'data' => ['message' => 'Configuración eliminada.'],
        ]);
    }
}
