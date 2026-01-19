<?php

namespace App\Policies;

use App\Models\Contribution;
use App\Models\User;

class ContributionPolicy extends BasePolicy
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
    public function view(?User $user, Contribution $contribution): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create models (Pay dues).
     */
    public function create(?User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can update the model (Verify).
     */
    public function update(?User $user, Contribution $contribution): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Contribution $contribution): bool
    {
        return $this->hasAnyRole($user, ['admin', 'bendahara']);
    }
}
