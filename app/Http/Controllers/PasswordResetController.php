<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user's email.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::broker('users')->sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    /**
     * Reset the user's password, revoke every session token, and issue a fresh one.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $user = null;

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $resetUser, string $password) use (&$user): void {
                $resetUser->forceFill(['password' => $password])->save();
                $resetUser->tokens()->delete();

                event(new PasswordReset($resetUser));

                $user = $resetUser;
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        $token = $user->createToken('auth_token', ['is_user'])->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Password has been reset.',
        ]);
    }
}
