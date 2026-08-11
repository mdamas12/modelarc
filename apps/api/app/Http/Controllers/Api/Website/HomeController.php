<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroGalleryResource;
use App\Http\Resources\HeroResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Resources\VirtualTourResource;
use App\Models\Hero;
use App\Models\HeroGallery;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\VirtualTour;
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
            ->with([
                'projectType',
                'categoryRef',
                'coverMedia',
                'virtualTour' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $featuredTours = VirtualTour::query()
            ->published()
            ->with([
                'project.coverMedia',
                'project.projectType',
                'project.categoryRef',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(12)
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
                'featured_tours' => VirtualTourResource::collection($featuredTours),
                'services' => ServiceResource::collection($services),
                'testimonials' => TestimonialResource::collection($testimonials),
                'settings' => $settings,
            ],
        ]);
    }
}
