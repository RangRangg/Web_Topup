<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Handle incoming payment gateway webhook notification.
     * Enforces HMAC-SHA256 signature verification, idempotency, and nominal tamper protection.
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Payment-Signature')
            ?? $request->header('X-Signature')
            ?? $request->input('signature_key')
            ?? $request->input('signature');

        if (empty($signature)) {
            return response()->json([
                'success' => false,
                'error' => 'Missing required X-Payment-Signature header or signature field.',
            ], 401);
        }

        $result = $this->paymentService->processWebhook(
            $request->all(),
            $request->getContent(),
            $signature
        );

        $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 400);

        return response()->json($result, $statusCode);
    }
}
