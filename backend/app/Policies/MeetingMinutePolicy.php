<?php

namespace App\Policies;

use App\Models\MeetingMinute;
use App\Models\User;

class MeetingMinutePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    public function view(?User $user, MeetingMinute $minute): bool
    {
        return $this->isAuthenticated($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function update(?User $user, MeetingMinute $minute): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function delete(?User $user, MeetingMinute $minute): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }
}

