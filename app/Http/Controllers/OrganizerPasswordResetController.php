<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Organizer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class OrganizerPasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given organizer's email.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::broker('organizers')->sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    /**
     * Reset the organizer's password, revoke every session token, and issue a fresh one.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $organizer = null;

        $status = Password::broker('organizers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Organizer $resetOrganizer, string $password) use (&$organizer): void {
                $resetOrganizer->forceFill(['password' => $password])->save();
                $resetOrganizer->tokens()->delete();

                event(new PasswordReset($resetOrganizer));

                $organizer = $resetOrganizer;
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        $token = $organizer->createToken('auth_token', ['is_organizer'])->plainTextToken;

        return response()->json([
            'organizer' => $organizer,
            'token' => $token,
            'message' => 'Password has been reset.',
        ]);
    }
}
