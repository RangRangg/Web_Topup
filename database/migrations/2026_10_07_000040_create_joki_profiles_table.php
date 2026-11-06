<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joki_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nickname');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->json('specialized_games')->nullable();
            $table->string('highest_rank')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('completed_orders_count')->default(0);
            $table->string('status')->default('available'); // available, busy, offline
            $table->boolean('is_verified')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joki_profiles');
    }
};
