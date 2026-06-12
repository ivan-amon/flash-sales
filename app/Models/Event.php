<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'total_tickets',
        'organizer_id',
        'city_id',
        'sale_starts_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_tickets' => 'integer',
            'sale_starts_at' => 'datetime',
        ];
    }

    /**
     * Get the tickets for the event.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * Get the city where the event takes place.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the orders for the event through tickets.
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Ticket::class);
    }
}
