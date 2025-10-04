<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cryptocurrency extends Model
{
    protected $fillable = [
        'coin_id',
        'symbol',
        'name',
        'is_tracked',
    ];

    protected $casts = [
        'is_tracked' => 'boolean',
    ];

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function latestPrice()
    {
        return $this->hasOne(PriceHistory::class)
                    ->latestOfMany('recorded_at');
    }

    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }
}
