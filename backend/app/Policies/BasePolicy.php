<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    /**
     * Check if user is admin
     */
    protected function isAdmin(?User $user): bool
    {
        return $user && $user->isAdmin() && $user->isActive();
    }

    /**
     * Check if user is ketua (chairman)
     */
    protected function isKetua(?User $user): bool
    {
        return $user && $user->isKetua() && $user->isActive();
    }

    /**
     * Check if user is bendahara (treasurer)
     */
    protected function isBendahara(?User $user): bool
    {
        return $user && $user->isBendahara() && $user->isActive();
    }

    /**
     * Check if user is sekretaris (secretary)
     */
    protected function isSekretaris(?User $user): bool
    {
        return $user && $user->isSekretaris() && $user->isActive();
    }

    /**
     * Check if user is anggota (member)
     */
    protected function isAnggota(?User $user): bool
    {
        return $user && $user->isAnggota() && $user->isActive();
    }

    /**
     * Check if user has any of the given roles
     */
    protected function hasAnyRole(?User $user, array $roles): bool
    {
        return $user && $user->hasAnyRole($roles) && $user->isActive();
    }

    /**
     * Check if user is authenticated and active
     */
    protected function isAuthenticated(?User $user): bool
    {
        return $user && $user->isActive();
    }
}
