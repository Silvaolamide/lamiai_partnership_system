<?php

namespace App\Policies;

use App\Models\ProgramPartner;
use App\Models\User;

class PartnerPolicy
{
    /**
     * Determine if the user can view a partner record.
     */
    public function view(User $user, ProgramPartner $partner): bool
    {
        // Admin can view any partner
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // User can view only their own partner records
        return $user->id === $partner->user_id;
    }

    /**
     * Determine if the user can view all partners.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can update a partner.
     */
    public function update(User $user, ProgramPartner $partner): bool
    {
        // Only admin can update partners
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can approve a partner.
     */
    public function approve(User $user, ProgramPartner $partner): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can reject a partner.
     */
    public function reject(User $user, ProgramPartner $partner): bool
    {
        return $user->hasRole('super_admin');
    }
}
