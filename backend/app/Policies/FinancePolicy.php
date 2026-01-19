<?php

namespace App\Policies;

use App\Models\User;

class FinancePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any finance records.
     */
    public function viewAny(?User $user): bool
    {
        // Admin, Ketua, Bendahara can view finance records
        return $this->hasAnyRole($user, ['admin', 'ketua', 'bendahara']);
    }

    /**
     * Determine whether the user can view the finance record.
     */
    public function view(?User $user, $finance): bool
    {
        // Admin, Ketua, Bendahara can view finance details
        return $this->hasAnyRole($user, ['admin', 'ketua', 'bendahara']);
    }

    /**
     * Determine whether the user can create finance records.
     */
    public function create(?User $user): bool
    {
        // Admin and Bendahara can create finance records
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can update the finance record.
     */
    public function update(?User $user, $finance): bool
    {
        // Admin and Bendahara can update finance records
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can delete the finance record.
     */
    public function delete(?User $user, $finance): bool
    {
        // Only Admin can delete finance records
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can view financial reports.
     */
    public function viewReports(?User $user): bool
    {
        // Admin, Ketua, Bendahara can view reports
        return $this->hasAnyRole($user, ['admin', 'ketua', 'bendahara']);
    }

    /**
     * Determine whether the user can manage wallets.
     */
    public function manageWallets(?User $user): bool
    {
        // Admin and Bendahara can manage wallets
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can upload receipts.
     */
    public function uploadReceipt(?User $user, $finance): bool
    {
        // Admin and Bendahara can upload receipts
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }
}
