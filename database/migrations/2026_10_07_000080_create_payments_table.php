<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('payment_code')->unique()->index();
            $table->string('payment_method')->default('qris'); // qris, bca, mandiri, dana, gopay
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, paid, expired, failed, refunded
            $table->string('payment_proof')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
