<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<\Database\Factories\CountryFactory> */
    use HasFactory;

    /**
     * The primary key for the model is the ISO 3166-1 alpha-2 code.
     *
     * @var string
     */
    protected $primaryKey = 'iso_code';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'iso_code',
    ];

    /**
     * Get the cities that belong to the country.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_code', 'iso_code');
    }
}
