<?php
namespace App\Services\V1;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User registered', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user and create a token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout a user by revoking their current token.
     */
    public function logout(User $user): void
    {
        // Revoke the current token
        $user->currentAccessToken()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }

    /**
     * Logout from all devices by revoking all tokens.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();

        Log::info('User logged out from all devices', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }
}
