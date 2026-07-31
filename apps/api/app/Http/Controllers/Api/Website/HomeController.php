<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $featuredProjects = Project::query()
            ->published()
            ->featured()
            ->with(['projectType', 'coverMedia'])
            ->latest('published_at')
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
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $settings = SiteSetting::query()
            ->whereIn('key', ['site_name', 'site_tagline', 'contact_email', 'contact_phone'])
            ->pluck('value', 'key');

        return response()->json([
            'data' => [
                'featured_projects' => ProjectResource::collection($featuredProjects),
                'services' => ServiceResource::collection($services),
                'testimonials' => TestimonialResource::collection($testimonials),
                'settings' => $settings,
            ],
        ]);
    }
}
