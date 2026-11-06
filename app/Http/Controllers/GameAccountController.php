<?php

namespace App\Http\Controllers;

use App\Services\GameAccountVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameAccountController extends Controller
{
    public function __construct(
        protected GameAccountVerifier $verifier
    ) {}

    /**
     * Check and verify in-game account player nickname in real-time.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game' => ['required', 'string', 'max:100'],
            'account_id' => ['required', 'string', 'max:100'],
            'account_server' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->verifier->verify(
            $validated['game'],
            $validated['account_id'],
            $validated['account_server'] ?? null
        );

        $status = $result['success'] ? 200 : 422;

        return response()->json($result, $status);
    }
}
