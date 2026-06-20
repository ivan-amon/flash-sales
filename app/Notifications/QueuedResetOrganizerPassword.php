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
        throw new \RuntimeException('Not implemented');
    }
}
