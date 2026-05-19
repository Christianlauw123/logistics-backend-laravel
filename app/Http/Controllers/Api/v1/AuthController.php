<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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
            if ($user->tokens()->where('name', 'web')->exists()) {
                $user->tokens()->where('name', 'web')->delete();
            }
        }

        if ($deviceType === 'mobile') {
            // max 2 tokens
            $mobileTokens = $user->tokens()->where('name', 'mobile')->count();

            if ($mobileTokens >= 2) {
                // delete oldest mobile token
                $user->tokens()
                    ->where('name', 'mobile')
                    ->oldest()
                    ->first()
                    ?->delete();
            }
        }

        $token = $user->createToken($deviceType)->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
