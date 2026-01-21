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
        return $user->hasPermission('view_finance') || $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can view the finance record.
     */
    public function view(?User $user, $finance): bool
    {
        return $user->hasPermission('view_finance') || $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can create finance records.
     */
    public function create(?User $user): bool
    {
        return $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can update the finance record.
     */
    public function update(?User $user, $finance): bool
    {
        return $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can delete the finance record.
     */
    public function delete(?User $user, $finance): bool
    {
        return $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can view financial reports.
     */
    public function viewReports(?User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    /**
     * Determine whether the user can manage wallets.
     */
    public function manageWallets(?User $user): bool
    {
        return $user->hasPermission('manage_finance');
    }

    /**
     * Determine whether the user can upload receipts.
     */
    public function uploadReceipt(?User $user, $finance): bool
    {
        return $user->hasPermission('manage_finance');
    }
}
