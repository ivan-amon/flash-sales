<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class OrganizerPasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given organizer's email.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Reset the organizer's password, revoke every session token, and issue a fresh one.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        throw new \RuntimeException('Not implemented');
    }
}
