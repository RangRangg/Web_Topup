<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_code',
        'idempotency_key',
        'user_id',
        'joki_profile_id',
        'promotion_id',
        'customer_name',
        'customer_email',
        'customer_whatsapp',
        'account_id',
        'account_server',
        'account_nickname',
        'login_via',
        'account_notes',
        'progress_note',
        'subtotal',
        'discount_amount',
        'total_amount',
        'order_status',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jokiProfile(): BelongsTo
    {
        return $this->belongsTo(JokiProfile::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isJoki(): bool
    {
        if ($this->joki_profile_id !== null) {
            return true;
        }

        if ($this->relationLoaded('orderItems')) {
            return $this->orderItems->contains(function ($item) {
                return $item->item_type === 'joki_service' || ! is_null($item->joki_service_id);
            });
        }

        return $this->orderItems()
            ->where(function ($q) {
                $q->where('item_type', 'joki_service')
                    ->orWhereNotNull('joki_service_id');
            })
            ->exists();
    }

    public function isTopUp(): bool
    {
        return ! $this->isJoki();
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusBadgeVariantAttribute(): string
    {
        $statusEnum = OrderStatus::tryFrom($this->order_status);
        if ($statusEnum) {
            return $statusEnum->badgeVariant();
        }

        return match ($this->order_status) {
            'completed' => 'success',
            'processing' => 'purple',
            'waiting_assignment' => 'info',
            'paid' => 'primary',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isTopUp()) {
            return match ($this->order_status) {
                'pending' => 'Menunggu Pembayaran',
                'paid' => 'Sudah Dibayar',
                'processing' => 'Sedang Diproses',
                'completed' => 'Selesai (Top Up Berhasil)',
                'cancelled' => 'Dibatalkan',
                default => ucfirst($this->order_status),
            };
        }

        $statusEnum = OrderStatus::tryFrom($this->order_status);
        if ($statusEnum) {
            return $statusEnum->label();
        }

        return ucfirst($this->order_status);
    }

    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->where('order_code', $value)
            ->orWhere('id', $value)
            ->first();
    }
}
