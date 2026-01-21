<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    public function view(?User $user, Announcement $announcement): bool
    {
        return $this->isAuthenticated($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function update(?User $user, Announcement $announcement): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function delete(?User $user, Announcement $announcement): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }
}

