<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'game_product_id',
        'joki_service_id',
        'item_type',
        'item_name',
        'price',
        'quantity',
        'subtotal',
        'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function gameProduct(): BelongsTo
    {
        return $this->belongsTo(GameProduct::class);
    }

    public function jokiService(): BelongsTo
    {
        return $this->belongsTo(JokiService::class);
    }
}
