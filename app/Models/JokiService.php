<?php

namespace App\Models;

use Database\Factories\JokiServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JokiService extends Model
{
    /** @use HasFactory<JokiServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'service_type',
        'rank_from',
        'rank_to',
        'price',
        'original_price',
        'unit',
        'estimated_duration',
        'is_popular',
        'is_active',
        'description',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function getFormattedOriginalPriceAttribute(): ?string
    {
        return $this->original_price ? 'Rp '.number_format($this->original_price, 0, ',', '.') : null;
    }

    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    public function getStartingPriceAttribute(): float
    {
        return (float) $this->price;
    }

    public function getFormattedStartingPriceAttribute(): string
    {
        return $this->getFormattedPriceAttribute();
    }

    public function getTargetAttribute(): string
    {
        if ($this->rank_to) {
            return $this->rank_from ? "{$this->rank_from} → {$this->rank_to}" : $this->rank_to;
        }

        return $this->rank_from ? "{$this->rank_from} → Selesai" : 'Target Sesuai Paket';
    }

    public function getEstimatedTimeAttribute(): string
    {
        return $this->estimated_duration;
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'available' : 'unavailable';
    }

    public function getImageAttribute(): string
    {
        return $this->game?->banner_url ?? $this->game?->icon_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80';
    }

    public function getRatingAttribute(): float
    {
        return 4.95;
    }
}
