<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

class CommissionPolicy
{
    /**
     * Determine if the user can view a commission.
     */
    public function view(User $user, Commission $commission): bool
    {
        // Admin can view any commission
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Partner can view only their own commissions
        return $user->id === $commission->partner->user_id;
    }

    /**
     * Determine if the user can view all commissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can approve a commission.
     */
    public function approve(User $user, Commission $commission): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can mark a commission as payable.
     */
    public function markPayable(User $user, Commission $commission): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can reverse a commission.
     */
    public function reverse(User $user, Commission $commission): bool
    {
        return $user->hasRole('super_admin');
    }
}
