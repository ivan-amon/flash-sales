<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\QueuedResetOrganizerPassword;
use App\Notifications\QueuedVerifyOrganizerEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Organizer extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'official_name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the email verification notification on the queue.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyOrganizerEmail);
    }

    /**
     * Send the password reset notification on the queue.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetOrganizerPassword($token));
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
