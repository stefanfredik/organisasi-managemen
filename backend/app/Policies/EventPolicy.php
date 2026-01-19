<?php

namespace App\Policies;

use App\Models\User;

class EventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(?User $user): bool
    {
        // All authenticated users can view events
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(?User $user, $event): bool
    {
        // All authenticated users can view event details
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(?User $user): bool
    {
        // Admin, Ketua, Sekretaris can create events
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(?User $user, $event): bool
    {
        // Admin, Ketua, Sekretaris can update events
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(?User $user, $event): bool
    {
        // Admin and Ketua can delete events
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can manage event participants.
     */
    public function manageParticipants(?User $user, $event): bool
    {
        // Admin, Ketua, Sekretaris can manage participants
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can upload event documentation.
     */
    public function uploadDocumentation(?User $user, $event): bool
    {
        // Admin, Ketua, Sekretaris can upload documentation
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }
}
