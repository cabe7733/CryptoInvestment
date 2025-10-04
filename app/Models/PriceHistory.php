<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    protected $fillable = [
        'cryptocurrency_id',
        'price_usd',
        'percent_change_1h',
        'percent_change_24h',
        'percent_change_7d',
        'market_cap',
        'volume_24h',
        'recorded_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:8',
        'percent_change_1h' => 'decimal:2',
        'percent_change_24h' => 'decimal:2',
        'percent_change_7d' => 'decimal:2',
        'market_cap' => 'decimal:2',
        'volume_24h' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }
}
