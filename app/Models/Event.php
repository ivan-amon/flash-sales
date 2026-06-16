<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'description',
        'total_tickets',
        'organizer_id',
        'city_id',
        'sale_starts_at',
        'event_starts_at',
        'cover_image_path',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'cover_image_url',
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
            'event_starts_at' => 'datetime',
        ];
    }

    /**
     * Get the publicly accessible URL for the event's cover image.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->cover_image_path
                ? asset('storage/'.$this->cover_image_path)
                : null,
        );
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
