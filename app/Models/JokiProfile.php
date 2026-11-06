<?php

namespace App\Models;

use Database\Factories\JokiProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JokiProfile extends Model
{
    /** @use HasFactory<JokiProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nickname',
        'phone',
        'bio',
        'specialized_games',
        'highest_rank',
        'rating',
        'completed_orders_count',
        'status',
        'is_verified',
    ];

    protected $casts = [
        'specialized_games' => 'array',
        'rating' => 'decimal:2',
        'completed_orders_count' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
