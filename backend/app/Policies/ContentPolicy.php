<?php

namespace App\Policies;

use App\Models\User;

class ContentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any content.
     */
    public function viewAny(?User $user): bool
    {
        // All authenticated users can view content
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the content.
     */
    public function view(?User $user, $content): bool
    {
        // All authenticated users can view content details
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create content.
     */
    public function create(?User $user): bool
    {
        // Admin, Ketua, Sekretaris can create content
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can update the content.
     */
    public function update(?User $user, $content): bool
    {
        // Admin, Ketua, Sekretaris can update content
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(?User $user, $content): bool
    {
        // Admin and Ketua can delete content
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can upload photos to albums.
     */
    public function uploadPhotos(?User $user, $album): bool
    {
        // Admin, Ketua, Sekretaris can upload photos
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can manage organization structure.
     */
    public function manageStructure(?User $user): bool
    {
        // Admin and Ketua can manage organization structure
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can manage vision/mission.
     */
    public function manageVisionMission(?User $user): bool
    {
        // Admin and Ketua can manage vision/mission
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }
}
