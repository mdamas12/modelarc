<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TestimonialInvitation extends Model
{
    protected $fillable = [
        'token',
        'project_id',
        'client_name',
        'client_email',
        'status',
        'testimonial_id',
        'created_by',
        'sent_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TestimonialInvitation $invitation): void {
            if (empty($invitation->token)) {
                $invitation->token = (string) Str::uuid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function publicUrl(): string
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        return $base.'/testimonios/'.$this->token;
    }
}
