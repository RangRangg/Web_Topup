<?php

namespace Database\Factories;

use App\Models\AIConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AIConversation>
 */
class AIConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Konsultasi Joki Game & Rekomendasi',
            'session_id' => Str::uuid(),
            'is_active' => true,
        ];
    }
}
