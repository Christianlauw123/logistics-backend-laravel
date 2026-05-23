<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_type' => ['required', 'in:web,mobile'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $deviceType = $request->device_type;

        // 🔥 LIMIT RULES
        if ($deviceType === 'web') {
            // max 1 token
            if ($user->tokens()->where('name', 'web_auth_token')->exists()) {
                $user->tokens()->where('name', 'web_auth_token')->delete();
            }
        }

        if ($deviceType === 'mobile') {
            // max 2 tokens
            $mobileTokens = $user->tokens()->where('name', 'mobile_auth_token')->count();

            if ($mobileTokens >= 2) {
                // delete oldest mobile token
                $user->tokens()
                    ->where('name', 'mobile_auth_token')
                    ->oldest()
                    ->first()
                    ?->delete();
            }
        }
        $expiration = match($deviceType) {
            'mobile'  => now()->addDays(30),  // Long lifespan for mobile
            'web' => now()->addHours(24), // Medium lifespan for desktop apps
        };

        $token = $user->createToken(
            name: $deviceType . '_auth_token',
            abilities: ['*'],
            expiresAt: $expiration
        )->plainTextToken;

        // setting up sanctum httpCookie
        $minutes = now()->diffInMinutes($expiration);

        return response()
            ->json([
                'message' => 'Login successful',
                'user' => new UserResource(
                    $this->userService->findOrFail($user->id)
                ),
                'expires_at' => $expiration->toISOString(),
                'token_for_testing' => app()->environment('local') ? $token : null,
            ])
            ->cookie(
                'access_token',
                $token,
                $minutes, // 7 days
                '/',
                app()->environment('production') ? config('app.app_domain') : null,
                app()->environment('production'), // secure = false for localhost HTTP
                true,  // httpOnly = true
                false,
                app()->environment('production') ? 'None' : 'Lax'
            );
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource(
                $this->userService->findOrFail($request->user()->id)
            )
        ]);
    }
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.'])->withoutCookie('access_token');
    }
}
