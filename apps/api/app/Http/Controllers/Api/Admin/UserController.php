<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly UserAccountService $accounts) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->with('roles')
            ->when($request->string('search')->toString(), function ($q, string $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate((int) $request->integer('per_page', 50));

        return UserResource::collection($users);
    }

    public function store(InviteUserRequest $request): JsonResponse
    {
        $result = $this->accounts->invite($request->validated(), $request->user());

        return (new UserResource($result['user']))
            ->additional([
                'meta' => [
                    'mail_sent' => $result['mail_sent'],
                    'mail_error' => $result['mail_error'],
                    'message' => $result['mail_sent']
                        ? 'Invitación enviada. El usuario debe activar su cuenta desde el email.'
                        : 'Usuario creado, pero no se pudo enviar el email de activación.',
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource(
            $this->accounts->update($user, $request->validated(), $request->user())
        );
    }

    public function block(Request $request, User $user): UserResource
    {
        return new UserResource($this->accounts->block($user, $request->user()));
    }

    public function unblock(User $user): UserResource
    {
        return new UserResource($this->accounts->unblock($user));
    }

    public function resendActivation(User $user): JsonResponse
    {
        $result = $this->accounts->resendActivation($user);

        return (new UserResource($result['user']))
            ->additional([
                'meta' => [
                    'mail_sent' => $result['mail_sent'],
                    'mail_error' => $result['mail_error'],
                    'message' => $result['mail_sent']
                        ? 'Email de activación reenviado.'
                        : 'No se pudo reenviar el email de activación.',
                ],
            ])
            ->response();
    }

    public function resetPassword(User $user): JsonResponse
    {
        $result = $this->accounts->sendPasswordReset($user);

        return (new UserResource($result['user']))
            ->additional([
                'meta' => [
                    'mail_sent' => $result['mail_sent'],
                    'mail_error' => $result['mail_error'],
                    'message' => $result['mail_sent']
                        ? 'Email de restablecimiento enviado.'
                        : 'No se pudo enviar el email de restablecimiento.',
                ],
            ])
            ->response();
    }
}
