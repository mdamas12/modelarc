<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\UserAccountToken;
use App\Services\UserAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountPasswordResetController extends Controller
{
    public function __construct(private readonly UserAccountService $accounts) {}

    public function show(string $token): JsonResponse
    {
        $record = $this->accounts->findValidToken($token, UserAccountToken::TYPE_PASSWORD_RESET);
        $user = $record->user;

        return response()->json([
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'expires_at' => $record->expires_at,
            ],
        ]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->accounts->resetPassword($token, $validated['password']);

        return response()->json([
            'data' => [
                'message' => 'Contraseña actualizada. Ya puedes iniciar sesión.',
                'user' => new UserResource($user),
                'login_url' => rtrim((string) config('app.admin_url', env('ADMIN_URL', '')), '/').'/login',
            ],
        ]);
    }
}
