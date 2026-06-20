<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetUserPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Get the password reset URL for the given user.
     */
    protected function resetUrl($notifiable): string
    {
        return config('app.frontend_url').'/password/reset?token='.$this->token
            .'&email='.urlencode($notifiable->getEmailForPasswordReset());
    }
}
