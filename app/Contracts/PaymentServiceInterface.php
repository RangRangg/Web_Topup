<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Create or initialize a payment record for an order.
     *
     * @param  array<string, mixed>  $options
     */
    public function createPayment(Order $order, string $paymentMethod, array $options = []): Payment;

    /**
     * Calculate payment administrative fee based on payment method and subtotal.
     */
    public function calculateFee(string $paymentMethod, float $amount): float;

    /**
     * Get list of all available payment methods and their fees/metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableMethods(): array;

    /**
     * Verify or check payment status.
     *
     * @return array<string, mixed>
     */
    public function checkStatus(Payment $payment): array;

    /**
     * Generate HMAC-SHA256 signature for webhook payload.
     */
    public function generateWebhookSignature(string|array $payload): string;

    /**
     * Verify authenticity of incoming webhook payload signature.
     */
    public function verifyWebhookSignature(string|array $payload, string $signature): bool;

    /**
     * Process incoming webhook notification idempotently and securely.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function processWebhook(array $payload, ?string $rawPayload = null, ?string $signature = null): array;

    /**
     * Simulate payment success transition (useful for dev/testing/demo).
     */
    public function simulatePaymentSuccess(Payment $payment): Payment;

    /**
     * Simulate payment failure transition.
     */
    public function simulatePaymentFailure(Payment $payment): Payment;

    /**
     * Simulate payment expiration transition.
     */
    public function simulatePaymentExpired(Payment $payment): Payment;
}
