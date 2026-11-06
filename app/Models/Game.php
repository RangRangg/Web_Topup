<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'publisher',
        'icon_url',
        'banner_url',
        'description',
        'badge',
        'is_active',
        'order_priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_priority' => 'integer',
    ];

    public function gameProducts(): HasMany
    {
        return $this->hasMany(GameProduct::class);
    }

    public function jokiServices(): HasMany
    {
        return $this->hasMany(JokiService::class);
    }
}
