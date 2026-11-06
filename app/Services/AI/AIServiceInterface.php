<?php

namespace App\Services\AI;

use App\Models\User;

interface AIServiceInterface
{
    /**
     * Process user prompt and return AI reply with structured recommendations and navigation.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{
     *     reply: string,
     *     recommendations: array<int, array{
     *         type: string,
     *         title: string,
     *         subtitle: string|null,
     *         price: string|null,
     *         badge: string|null,
     *         url: string
     *     }>,
     *     navigation: array<int, array{label: string, url: string}>,
     *     source: string
     * }
     */
    public function ask(string $prompt, ?User $user = null, array $history = []): array;
}
