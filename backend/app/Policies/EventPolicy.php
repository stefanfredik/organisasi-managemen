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
        return $user->hasPermission('view_events') || $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(?User $user, $event): bool
    {
        return $user->hasPermission('view_events') || $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(?User $user): bool
    {
        return $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(?User $user, $event): bool
    {
        return $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(?User $user, $event): bool
    {
        return $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can manage event participants.
     */
    public function manageParticipants(?User $user, $event): bool
    {
        return $user->hasPermission('manage_events');
    }

    /**
     * Determine whether the user can upload event documentation.
     */
    public function uploadDocumentation(?User $user, $event): bool
    {
        return $user->hasPermission('manage_events');
    }
}
