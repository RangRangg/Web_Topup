<?php

namespace Database\Factories;

use App\Models\AIConversation;
use App\Models\AIMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AIMessage>
 */
class AIMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ai_conversation_id' => AIConversation::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'meta' => null,
        ];
    }
}
