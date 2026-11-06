<?php

namespace App\Models;

use Database\Factories\GameProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameProduct extends Model
{
    /** @use HasFactory<GameProductFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'code',
        'category',
        'price',
        'original_price',
        'stock',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
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
}
