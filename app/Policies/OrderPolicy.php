<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific order.
     *
     * Rules:
     * - Admin can view any order.
     * - Joki can ONLY view orders that are:
     *   a) Already assigned to them ($order->joki_profile_id === $user->jokiProfile->id)
     *   b) Available for them (unassigned, status waiting_assignment, paid, or pending)
     * - Customer can ONLY view their own orders ($order->user_id === $user->id).
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isJoki()) {
            $jokiProfileId = $user->jokiProfile?->id;

            // 1. Order is already assigned to this joki booster
            if ($jokiProfileId && $order->joki_profile_id === $jokiProfileId) {
                return true;
            }

            // 2. Order is available for them to take (unassigned and in waiting_assignment, paid, or pending)
            if (is_null($order->joki_profile_id) && in_array($order->order_status, [
                OrderStatus::WAITING_ASSIGNMENT->value,
                OrderStatus::PAID->value,
                OrderStatus::PENDING->value,
                'waiting_assignment',
                'paid',
                'pending',
            ])) {
                return true;
            }

            // Not assigned to them and not available (e.g. assigned to another booster)
            return false;
        }

        // Customer can ONLY view their own order
        return $order->user_id !== null && $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can assign or claim the order.
     *
     * - Admin can assign any booster to any order.
     * - Joki can self-claim an available unassigned order.
     */
    public function assign(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isJoki()) {
            return is_null($order->joki_profile_id) && in_array($order->order_status, [
                OrderStatus::WAITING_ASSIGNMENT->value,
                OrderStatus::PAID->value,
                'waiting_assignment',
                'paid',
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can update the order progress.
     *
     * - Admin can update any order.
     * - Joki can update only orders assigned to them.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isJoki()) {
            return $user->jokiProfile && $order->joki_profile_id === $user->jokiProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can submit a review for the order.
     *
     * - Only the customer who owns the order can submit a review.
     * - The order MUST be in COMPLETED status.
     * - Order must not already have a review.
     */
    public function review(User $user, Order $order): bool
    {
        if ($order->user_id !== $user->id) {
            return false;
        }

        if (! in_array($order->order_status, [OrderStatus::COMPLETED->value, 'completed'])) {
            return false;
        }

        return ! $order->reviews()->exists();
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
