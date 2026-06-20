<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetOrganizerPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Get the password reset URL for the given organizer.
     */
    protected function resetUrl($notifiable): string
    {
        return config('app.frontend_url').'/organizer/password/reset?token='.$this->token
            .'&email='.urlencode($notifiable->getEmailForPasswordReset());
    }
}
