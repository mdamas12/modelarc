<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroGalleryResource;
use App\Http\Resources\HeroResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Hero;
use App\Models\HeroGallery;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $hero = Hero::singleton();
        $heroGalleries = HeroGallery::query()
            ->where('hero_id', $hero->id)
            ->published()
            ->ordered()
            ->limit(4)
            ->get();

        $featuredProjects = Project::query()
            ->published()
            ->featured()
            ->with(['projectType', 'coverMedia'])
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $services = Service::query()
            ->active()
            ->with('image')
            ->orderBy('sort_order')
            ->get();

        $testimonials = Testimonial::query()
            ->active()
            ->with(['clientPhoto', 'project:id,title,slug'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(24)
            ->get();

        $settings = SiteSetting::query()
            ->whereIn('key', ['site_name', 'site_tagline', 'contact_email', 'contact_phone'])
            ->pluck('value', 'key');

        return response()->json([
            'data' => [
                'hero' => (new HeroResource($hero))->resolve(),
                'hero_galleries' => HeroGalleryResource::collection($heroGalleries)->resolve(),
                'featured_projects' => ProjectResource::collection($featuredProjects),
                'services' => ServiceResource::collection($services),
                'testimonials' => TestimonialResource::collection($testimonials),
                'settings' => $settings,
            ],
        ]);
    }
}
