<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class QueuedVerifyOrganizerEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verification URL for the given organizer.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'organizer.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
