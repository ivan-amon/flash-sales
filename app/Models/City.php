<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'country_code',
    ];

    /**
     * Get the country that owns the city.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code');
    }

    /**
     * Get the events that take place in the city.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
