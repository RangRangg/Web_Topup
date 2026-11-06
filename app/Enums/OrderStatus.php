<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case WAITING_ASSIGNMENT = 'waiting_assignment';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PAID => 'Sudah Dibayar',
            self::WAITING_ASSIGNMENT => 'Menunggu Booster',
            self::PROCESSING => 'Sedang Dikerjakan',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'primary',
            self::WAITING_ASSIGNMENT => 'info',
            self::PROCESSING => 'purple',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
