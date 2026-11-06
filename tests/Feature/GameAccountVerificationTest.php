<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameAccountVerificationTest extends TestCase
{
    /**
     * Test game account verification endpoint requires game and account_id.
     */
    public function test_verification_requires_required_parameters(): void
    {
        $response = $this->getJson('/api/check-game-account');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['game', 'account_id']);
    }

    /**
     * Test Mobile Legends live verification returns nickname when valid.
     */
    public function test_mobile_legends_verification_returns_nickname(): void
    {
        Http::fake([
            'https://api.isan.eu.org/nickname/ml*' => Http::response([
                'success' => true,
                'game' => 'Mobile Legends: Bang Bang',
                'id' => 241254030,
                'server' => 9259,
                'name' => 'Aetherel.',
                'country' => 'Indonesia',
            ], 200),
        ]);

        $response = $this->getJson('/api/check-game-account?game=mobile-legends&account_id=241254030&account_server=9259');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'nickname' => 'Aetherel.',
            'game' => 'Mobile Legends: Bang Bang',
            'country' => 'Indonesia',
        ]);
    }

    /**
     * Test Mobile Legends verification fails when account not found.
     */
    public function test_mobile_legends_verification_fails_for_invalid_account(): void
    {
        Http::fake([
            'https://api.isan.eu.org/nickname/ml*' => Http::response([
                'success' => false,
            ], 500),
        ]);

        $response = $this->getJson('/api/check-game-account?game=mobile-legends&account_id=999999999&account_server=9999');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
