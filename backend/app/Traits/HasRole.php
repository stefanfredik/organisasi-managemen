<?php

namespace App\Traits;

trait HasRole
{
    /**
     * Check if user has a specific role or position.
     * 'admin'/'member' checks against users.role.
     * 'ketua'/'bendahara'/'sekretaris'/'anggota' checks against members.position.
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        $userRoles = ['admin', 'member'];

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                if ($this->role === $role) return true;
            } else {
                if ($this->member?->position?->code === $role) return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given roles/positions.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a member (non-admin).
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if user holds ketua (chairman) position.
     */
    public function isKetua(): bool
    {
        return $this->member?->position?->code === 'ketua';
    }

    /**
     * Check if user holds bendahara (treasurer) position.
     */
    public function isBendahara(): bool
    {
        return $this->member?->position?->code === 'bendahara';
    }

    /**
     * Check if user holds sekretaris (secretary) position.
     */
    public function isSekretaris(): bool
    {
        return $this->member?->position?->code === 'sekretaris';
    }

    /**
     * Check if user holds anggota (regular member) position.
     */
    public function isAnggota(): bool
    {
        return $this->member?->position?->code === 'anggota';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}
