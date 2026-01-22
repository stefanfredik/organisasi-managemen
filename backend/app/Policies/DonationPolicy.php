<?php

namespace App\Policies;

use App\Models\User;

class DonationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any donations.
     */
    public function viewAny(?User $user): bool
    {
        // All authenticated users can view donations (public info)
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the donation.
     */
    public function view(?User $user, $donation): bool
    {
        // All authenticated users can view donation details
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create donations.
     */
    public function create(?User $user): bool
    {
        // Admin, Ketua, Sekretaris can create donation programs
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can update the donation.
     */
    public function update(?User $user, $donation): bool
    {
        // Admin, Ketua, Sekretaris can update donation programs
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete the donation.
     */
    public function delete(?User $user, $donation): bool
    {
        // Only Admin can delete donation programs
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can record donation transactions (Manual Entry).
     */
    public function recordTransaction(?User $user, $donation): bool
    {
        // Admin and Bendahara can record donation transactions
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can make a donation payment (Member).
     */
    public function makePayment(?User $user, $donation): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can verify donation transactions.
     */
    public function verifyTransaction(?User $user, $donation): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can view donation reports.
     */
    public function viewReports(?User $user, $donation): bool
    {
        // Admin, Ketua, Bendahara can view detailed reports
        return $this->hasAnyRole($user, ['admin', 'ketua', 'bendahara']);
    }
}
