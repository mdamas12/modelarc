<?php

namespace App\Services;

use App\Models\TourHotspot;
use App\Models\TourScene;

class HotspotService
{
    public function create(TourScene $scene, array $data): TourHotspot
    {
        $data['tour_scene_id'] = $scene->id;
        $data['sort_order'] = $data['sort_order'] ?? ($scene->hotspots()->max('sort_order') + 1);

        return TourHotspot::query()->create($data)->load(['media', 'targetScene']);
    }

    public function update(TourHotspot $hotspot, array $data): TourHotspot
    {
        $hotspot->update($data);

        return $hotspot->fresh(['media', 'targetScene']);
    }

    public function delete(TourHotspot $hotspot): void
    {
        $hotspot->delete();
    }
}
