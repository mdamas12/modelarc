<?php

namespace App\Services;

use App\Mail\TestimonialInvitationMail;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\TestimonialInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class TestimonialInvitationService
{
    /**
     * @param  array{project_id:int, client_name:string, client_email:string}  $data
     * @return array{invitation: TestimonialInvitation, mail_sent: bool, mail_error: ?string}
     */
    public function createAndSend(array $data, ?int $userId = null): array
    {
        $project = Project::query()->findOrFail($data['project_id']);

        $invitation = TestimonialInvitation::query()->create([
            'project_id' => $project->id,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'status' => 'pending',
            'created_by' => $userId,
            'sent_at' => null,
        ])->load(['project', 'creator']);

        [$mailSent, $mailError] = $this->sendMail($invitation);

        if ($mailSent) {
            $invitation->update(['sent_at' => now()]);
        }

        return [
            'invitation' => $invitation->fresh(['project', 'creator']),
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
        ];
    }

    /**
     * @return array{invitation: TestimonialInvitation, mail_sent: bool, mail_error: ?string}
     */
    public function resend(TestimonialInvitation $invitation): array
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'Esta invitación ya no está activa.',
            ]);
        }

        $invitation->load('project');
        [$mailSent, $mailError] = $this->sendMail($invitation);

        if ($mailSent) {
            $invitation->update(['sent_at' => now()]);
        }

        return [
            'invitation' => $invitation->fresh(['project']),
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
        ];
    }

    /**
     * @return array{0: bool, 1: ?string}
     */
    protected function sendMail(TestimonialInvitation $invitation): array
    {
        try {
            Mail::to($invitation->client_email)->send(
                new TestimonialInvitationMail($invitation->loadMissing('project'))
            );

            return [true, null];
        } catch (Throwable $e) {
            Log::error('No se pudo enviar invitación de testimonio', [
                'invitation_id' => $invitation->id,
                'email' => $invitation->client_email,
                'error' => $e->getMessage(),
            ]);

            return [false, $e->getMessage()];
        }
    }

    public function findPublicByToken(string $token): TestimonialInvitation
    {
        return TestimonialInvitation::query()
            ->with(['project:id,title,slug,category,location,publication_status'])
            ->where('token', $token)
            ->firstOrFail();
    }

    /**
     * @param  array{rating:int, quote:string, allow_publish:bool, client_name?:string}  $data
     */
    public function submit(TestimonialInvitation $invitation, array $data): Testimonial
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'token' => 'Este enlace ya fue utilizado o no está disponible.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $data) {
            $locked = TestimonialInvitation::query()
                ->whereKey($invitation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isPending()) {
                throw ValidationException::withMessages([
                    'token' => 'Este enlace ya fue utilizado o no está disponible.',
                ]);
            }

            $testimonial = Testimonial::query()->create([
                'client_name' => $data['client_name'] ?? $locked->client_name,
                'project_id' => $locked->project_id,
                'quote' => $data['quote'],
                'rating' => $data['rating'],
                'sort_order' => ((int) Testimonial::query()->max('sort_order')) + 1,
                'status' => ! empty($data['allow_publish']) ? 'active' : 'inactive',
            ]);

            $locked->update([
                'status' => 'completed',
                'testimonial_id' => $testimonial->id,
                'completed_at' => now(),
            ]);

            return $testimonial->load('project');
        });
    }
}
