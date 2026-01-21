<?php

namespace App\Policies;

use App\Models\User;

class MemberPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any members.
     */
    public function viewAny(?User $user): bool
    {
        return $user->hasPermission('view_members') || $user->hasPermission('manage_members');
    }

    /**
     * Determine whether the user can view the member.
     */
    public function view(?User $user, $member): bool
    {
        if ($user->hasPermission('view_members') || $user->hasPermission('manage_members')) {
            return true;
        }

        // Anggota can view own profile
        if ($user && $member->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create members.
     */
    public function create(?User $user): bool
    {
        return $user->hasPermission('manage_members');
    }

    /**
     * Determine whether the user can update the member.
     */
    public function update(?User $user, $member): bool
    {
        if ($user->hasPermission('manage_members')) {
            return true;
        }

        // Anggota can update own profile (limited fields)
        if ($user && $member->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the member.
     */
    public function delete(?User $user, $member): bool
    {
        return $user->hasPermission('manage_members');
    }

    /**
     * Determine whether the user can restore the member.
     */
    public function restore(?User $user, $member): bool
    {
        // Only Admin can restore members
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the member.
     */
    public function forceDelete(?User $user, $member): bool
    {
        // Only Admin can force delete members
        return $this->isAdmin($user);
    }
}
