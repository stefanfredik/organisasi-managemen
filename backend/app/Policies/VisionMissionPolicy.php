<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisionMission;

class VisionMissionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any vision/missions.
     */
    public function viewAny(?User $user): bool
    {
        // All authenticated users can view vision/missions
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the vision/mission.
     */
    public function view(?User $user, VisionMission $visionMission): bool
    {
        // All authenticated users can view vision/mission details
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create vision/missions.
     */
    public function create(?User $user): bool
    {
        // Only Admin and Ketua can create vision/missions
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can update the vision/mission.
     */
    public function update(?User $user, VisionMission $visionMission): bool
    {
        // Only Admin and Ketua can update vision/missions
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can delete the vision/mission.
     */
    public function delete(?User $user, VisionMission $visionMission): bool
    {
        // Only Admin can delete vision/missions
        return $this->hasRole($user, 'admin');
    }

    /**
     * Determine whether the user can activate/deactivate vision/mission.
     */
    public function changeStatus(?User $user, VisionMission $visionMission): bool
    {
        // Only Admin and Ketua can change status
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }
}
