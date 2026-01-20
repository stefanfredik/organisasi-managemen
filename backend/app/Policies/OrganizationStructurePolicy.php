<?php

namespace App\Policies;

use App\Models\OrganizationStructure;
use App\Models\User;

class OrganizationStructurePolicy extends BasePolicy
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
    public function view(?User $user, OrganizationStructure $organizationStructure): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, OrganizationStructure $organizationStructure): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, OrganizationStructure $organizationStructure): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }
}
