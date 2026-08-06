<?php

namespace App\Services;

use App\Mail\UserAccountMail;
use App\Models\User;
use App\Models\UserAccountToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserAccountService
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BLOCKED = 'blocked';

    /**
     * @param  array{name:string,email:string,role:string}  $data
     * @return array{user: User, mail_sent: bool, mail_error: ?string, activation_url: string}
     */
    public function invite(array $data, ?User $actor = null): array
    {
        $role = $this->normalizeRole($data['role'], $actor);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Str::random(48),
            'status' => self::STATUS_PENDING,
        ]);

        $user->syncRoles([$role]);

        [$token, $plain] = $this->issueToken($user, UserAccountToken::TYPE_ACTIVATION);
        $url = $this->activationUrl($plain);
        [$mailSent, $mailError] = $this->sendAccountMail(
            $user,
            'Bienvenido a Modelarc — activa tu cuenta',
            'activation',
            $url,
        );

        return [
            'user' => $user->fresh()->load('roles'),
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
            'activation_url' => $url,
        ];
    }

    /**
     * @param  array{name?:string,email?:string,role?:string}  $data
     */
    public function update(User $user, array $data, ?User $actor = null): User
    {
        if (array_key_exists('name', $data)) {
            $user->name = trim((string) $data['name']);
        }

        if (array_key_exists('email', $data)) {
            $user->email = strtolower(trim((string) $data['email']));
        }

        $user->save();

        if (array_key_exists('role', $data) && $data['role'] !== null && $data['role'] !== '') {
            $user->syncRoles([$this->normalizeRole((string) $data['role'], $actor)]);
        }

        return $user->fresh()->load('roles');
    }

    public function block(User $user, ?User $actor = null): User
    {
        if ($actor && (int) $actor->id === (int) $user->id) {
            throw ValidationException::withMessages([
                'user' => 'No puedes bloquear tu propia cuenta.',
            ]);
        }

        $user->update(['status' => self::STATUS_BLOCKED]);
        $user->tokens()->delete();

        return $user->fresh()->load('roles');
    }

    public function unblock(User $user): User
    {
        if ($user->status === self::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'user' => 'Esta cuenta aún está pendiente de activación.',
            ]);
        }

        $user->update(['status' => self::STATUS_ACTIVE]);

        return $user->fresh()->load('roles');
    }

    /**
     * @return array{user: User, mail_sent: bool, mail_error: ?string, activation_url: string}
     */
    public function resendActivation(User $user): array
    {
        if ($user->status !== self::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'user' => 'Solo se puede reenviar la activación a cuentas pendientes.',
            ]);
        }

        [$token, $plain] = $this->issueToken($user, UserAccountToken::TYPE_ACTIVATION);
        $url = $this->activationUrl($plain);
        [$mailSent, $mailError] = $this->sendAccountMail(
            $user,
            'Activa tu cuenta de Modelarc',
            'activation',
            $url,
        );

        return [
            'user' => $user->fresh()->load('roles'),
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
            'activation_url' => $url,
        ];
    }

    /**
     * @return array{user: User, mail_sent: bool, mail_error: ?string, reset_url: string}
     */
    public function sendPasswordReset(User $user): array
    {
        if ($user->status === self::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'user' => 'La cuenta aún no está activada. Reenvía la invitación de activación.',
            ]);
        }

        if ($user->status === self::STATUS_BLOCKED) {
            throw ValidationException::withMessages([
                'user' => 'No se puede restablecer la contraseña de una cuenta bloqueada.',
            ]);
        }

        [$token, $plain] = $this->issueToken($user, UserAccountToken::TYPE_PASSWORD_RESET);
        $url = $this->resetUrl($plain);
        [$mailSent, $mailError] = $this->sendAccountMail(
            $user,
            'Restablece tu contraseña — Modelarc',
            'password_reset',
            $url,
        );

        return [
            'user' => $user->fresh()->load('roles'),
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
            'reset_url' => $url,
        ];
    }

    public function findValidToken(string $plainToken, string $type): UserAccountToken
    {
        $hash = hash('sha256', $plainToken);

        /** @var UserAccountToken|null $token */
        $token = UserAccountToken::query()
            ->with('user.roles')
            ->where('token', $hash)
            ->where('type', $type)
            ->first();

        if (! $token || ! $token->isValid() || ! $token->user) {
            throw ValidationException::withMessages([
                'token' => 'Este enlace no es válido o ya fue utilizado.',
            ]);
        }

        return $token;
    }

    public function activate(string $plainToken, string $password): User
    {
        $token = $this->findValidToken($plainToken, UserAccountToken::TYPE_ACTIVATION);
        $user = $token->user;

        if ($user->status === self::STATUS_BLOCKED) {
            throw ValidationException::withMessages([
                'token' => 'Esta cuenta está bloqueada. Contacta a un administrador.',
            ]);
        }

        $user->update([
            'password' => $password,
            'status' => self::STATUS_ACTIVE,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $this->consumeToken($token);
        $this->invalidateOpenTokens($user, UserAccountToken::TYPE_ACTIVATION);

        return $user->fresh()->load('roles');
    }

    public function resetPassword(string $plainToken, string $password): User
    {
        $token = $this->findValidToken($plainToken, UserAccountToken::TYPE_PASSWORD_RESET);
        $user = $token->user;

        if ($user->status !== self::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'token' => 'Solo cuentas activas pueden restablecer la contraseña.',
            ]);
        }

        $user->update(['password' => $password]);
        $user->tokens()->delete();

        $this->consumeToken($token);
        $this->invalidateOpenTokens($user, UserAccountToken::TYPE_PASSWORD_RESET);

        return $user->fresh()->load('roles');
    }

    /**
     * @return array{0: UserAccountToken, 1: string}
     */
    protected function issueToken(User $user, string $type, int $days = 7): array
    {
        $this->invalidateOpenTokens($user, $type);

        $plain = Str::random(64);

        $token = UserAccountToken::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addDays($days),
            'used_at' => null,
        ]);

        return [$token, $plain];
    }

    protected function consumeToken(UserAccountToken $token): void
    {
        $token->update(['used_at' => now()]);
    }

    protected function invalidateOpenTokens(User $user, string $type): void
    {
        UserAccountToken::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    protected function normalizeRole(string $role, ?User $actor = null): string
    {
        $role = strtolower(trim($role));
        $allowed = ['admin', 'editor'];

        if ($actor?->hasRole('superadmin')) {
            $allowed[] = 'superadmin';
        }

        if (! in_array($role, $allowed, true)) {
            throw ValidationException::withMessages([
                'role' => 'Rol no permitido.',
            ]);
        }

        return $role;
    }

    protected function adminBaseUrl(): string
    {
        return rtrim((string) config('app.admin_url', env('ADMIN_URL', '')), '/');
    }

    protected function activationUrl(string $plain): string
    {
        return $this->adminBaseUrl().'/activar/'.$plain;
    }

    protected function resetUrl(string $plain): string
    {
        return $this->adminBaseUrl().'/restablecer/'.$plain;
    }

    /**
     * @return array{0: bool, 1: ?string}
     */
    protected function sendAccountMail(User $user, string $subject, string $kind, string $actionUrl): array
    {
        $html = view('emails.user-account', [
            'user' => $user,
            'kind' => $kind,
            'actionUrl' => $actionUrl,
            'adminUrl' => $this->adminBaseUrl(),
        ])->render();

        $resendKey = (string) config('services.resend.key', '');

        try {
            if ($resendKey !== '') {
                $fromAddress = (string) config('mail.from.address', 'info@modelarcve.com');
                $fromName = (string) config('mail.from.name', 'Modelarc');

                $response = Http::withToken($resendKey)
                    ->acceptJson()
                    ->timeout(20)
                    ->post('https://api.resend.com/emails', [
                        'from' => "{$fromName} <{$fromAddress}>",
                        'to' => [$user->email],
                        'subject' => $subject,
                        'html' => $html,
                    ]);

                if ($response->failed()) {
                    throw new \RuntimeException('Resend email failed: '.$response->body());
                }

                return [true, null];
            }

            Mail::to($user->email)->sendNow(new UserAccountMail($user, $subject, $kind, $actionUrl));

            return [true, null];
        } catch (Throwable $e) {
            Log::error('No se pudo enviar email de cuenta de usuario', [
                'user_id' => $user->id,
                'email' => $user->email,
                'kind' => $kind,
                'error' => $e->getMessage(),
            ]);

            return [false, $e->getMessage()];
        }
    }
}
