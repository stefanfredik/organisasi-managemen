<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Wallet $wallet): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Wallet $wallet): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Wallet $wallet): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }
}
