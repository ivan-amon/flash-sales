<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organizer;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrganizerEmailVerificationController extends Controller
{
    /**
     * Verify the organizer's email address from a signed link, then redirect to the SPA.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $organizer = Organizer::findOrFail($id);

        if (! hash_equals($hash, sha1($organizer->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (! $organizer->hasVerifiedEmail()) {
            $organizer->markEmailAsVerified();

            event(new Verified($organizer));
        }

        return redirect()->away(config('app.frontend_url').'/email/verified');
    }

    /**
     * Resend the email verification notification to the authenticated organizer.
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }
}
