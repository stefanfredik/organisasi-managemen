<?php

namespace App\Traits;

trait HasRole
{
    /**
     * Check if user has a specific role or roles
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user has all of the given roles
     * Note: This is less useful since a user can only have one role
     *
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        return count($roles) === 1 && $this->hasRole($roles[0]);
    }

    /**
     * Check if user is an admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is ketua (chairman)
     *
     * @return bool
     */
    public function isKetua(): bool
    {
        return $this->role === 'ketua';
    }

    /**
     * Check if user is bendahara (treasurer)
     *
     * @return bool
     */
    public function isBendahara(): bool
    {
        return $this->role === 'bendahara';
    }

    /**
     * Check if user is sekretaris (secretary)
     *
     * @return bool
     */
    public function isSekretaris(): bool
    {
        return $this->role === 'sekretaris';
    }

    /**
     * Check if user is anggota (member)
     *
     * @return bool
     */
    public function isAnggota(): bool
    {
        return $this->role === 'anggota';
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is inactive
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}
