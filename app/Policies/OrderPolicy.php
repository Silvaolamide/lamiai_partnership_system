<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view an order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin can view any order
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Customer can view only their own orders
        return $user->id === $order->customer_id;
    }

    /**
     * Determine if the user can view all orders (admin only).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can update an order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admin can update orders
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can mark an order as paid.
     */
    public function markPaid(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can refund an order.
     */
    public function refund(User $user, Order $order): bool
    {
        return $user->hasRole('super_admin');
    }
}
