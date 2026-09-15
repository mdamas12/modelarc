<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\VirtualTour;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function kpis(): array
    {
        $leadsByStatus = Lead::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $projectsByPublication = Project::query()
            ->select('publication_status', DB::raw('count(*) as total'))
            ->groupBy('publication_status')
            ->pluck('total', 'publication_status');

        $recentProjects = Project::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'slug', 'category', 'publication_status', 'views_count', 'created_at']);

        $topProjects = Project::query()
            ->where('views_count', '>', 0)
            ->orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'title', 'views_count'])
            ->map(fn (Project $project) => [
                'name' => $project->title,
                'views' => (int) $project->views_count,
            ])
            ->values()
            ->all();

        $activity = ActivityLog::query()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'title' => $log->action,
                'description' => $log->description,
                'time' => optional($log->created_at)->diffForHumans(),
            ])
            ->values()
            ->all();

        return [
            'projects_total' => Project::query()->count(),
            'projects_published' => Project::query()->published()->count(),
            'projects_featured' => Project::query()->featured()->count(),
            'projects_with_tour' => Project::query()->where('has_virtual_tour', true)->count(),
            'tours_published' => VirtualTour::query()->published()->count(),
            'services_active' => Service::query()->active()->count(),
            'testimonials_active' => Testimonial::query()->active()->count(),
            'leads_total' => Lead::query()->count(),
            'leads_new' => Lead::query()->where('status', 'new')->count(),
            'leads_by_status' => $leadsByStatus,
            'projects_by_publication' => $projectsByPublication,
            'recent_leads' => Lead::query()->latest()->limit(5)->get(),
            'recent_projects' => $recentProjects,
            'top_projects' => $topProjects,
            'activity' => $activity,
            // Visitas de sitio / storage: sin tracking real ni métrica fiable en Fase 1.
            'analytics_pending' => true,
        ];
    }
}
