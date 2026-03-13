<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Contracts\Auth\Authenticatable;

class EventPolicy
{
    /**
     * Determine whether the organizer can create events.
     */
    public function create(Authenticatable $organizer): bool
    {
        return $organizer instanceof Organizer;
    }

    /**
     * Determine whether the organizer can update the event.
     */
    public function update(Authenticatable $organizer, Event $event): bool
    {
        return $organizer instanceof Organizer && $event->organizer_id === $organizer->id;
    }

    /**
     * Determine whether the organizer can delete the event.
     */
    public function delete(Authenticatable $organizer, Event $event): bool
    {
        return $organizer instanceof Organizer && $event->organizer_id === $organizer->id;
    }
}
