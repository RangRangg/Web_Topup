<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('game_product_id')->nullable()->constrained('game_products')->nullOnDelete();
            $table->foreignId('joki_service_id')->nullable()->constrained('joki_services')->nullOnDelete();
            $table->string('item_type')->default('joki_service'); // joki_service, product
            $table->string('item_name');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->json('meta')->nullable(); // extra info like rank_from, rank_to, hero request
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
