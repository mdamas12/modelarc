<?php

namespace App\Services;

use App\Jobs\SendContactLeadMailJob;
use App\Jobs\SendContactLeadWhatsAppJob;
use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeadService
{
    public function create(array $data): Lead
    {
        $data['status'] = $data['status'] ?? 'new';
        $data['source'] = $data['source'] ?? 'website';

        /** @var Lead $lead */
        $lead = Lead::query()->create($data);

        ActivityLog::query()->create([
            'user_id' => null,
            'action' => 'lead.created',
            'description' => "Nuevo lead: {$lead->name} ({$lead->email})",
            'subject_type' => Lead::class,
            'subject_id' => $lead->id,
        ]);

        $this->notifyTeam($lead);

        return $lead;
    }

    protected function notifyTeam(Lead $lead): void
    {
        try {
            SendContactLeadMailJob::dispatch($lead->id);
        } catch (Throwable $e) {
            Log::error('No se pudo encolar el email de nueva solicitud de contacto', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            SendContactLeadWhatsAppJob::dispatch($lead->id);
        } catch (Throwable $e) {
            Log::error('No se pudo encolar el aviso WhatsApp de nueva solicitud de contacto', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Lead::query()
            ->with('project:id,title,slug')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['country'] ?? null, fn ($q, $country) => $q->where('country', $country))
            ->when($filters['state'] ?? null, fn ($q, $state) => $q->where('state', $state))
            ->when($filters['budget_range'] ?? null, fn ($q, $budget) => $q->where('budget_range', $budget))
            ->when($filters['project_type'] ?? null, fn ($q, $type) => $q->where('project_type', $type))
            ->when($filters['search'] ?? null, function ($q, string $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh('project');
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    /**
     * Distinct values for admin filter dropdowns.
     *
     * @return array{countries: list<string>, states: list<string>, project_types: list<string>}
     */
    public function filterOptions(): array
    {
        return [
            'countries' => Lead::query()
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->distinct()
                ->orderBy('country')
                ->pluck('country')
                ->values()
                ->all(),
            'states' => Lead::query()
                ->whereNotNull('state')
                ->where('state', '!=', '')
                ->distinct()
                ->orderBy('state')
                ->pluck('state')
                ->values()
                ->all(),
            'project_types' => Lead::query()
                ->whereNotNull('project_type')
                ->where('project_type', '!=', '')
                ->distinct()
                ->orderBy('project_type')
                ->pluck('project_type')
                ->values()
                ->all(),
        ];
    }
}
