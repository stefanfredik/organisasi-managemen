<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;

class AlbumPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any albums.
     */
    public function viewAny(?User $user): bool
    {
        // All authenticated users can view albums
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the album.
     */
    public function view(?User $user, Album $album): bool
    {
        // Public albums can be viewed by anyone
        if ($album->status === 'public') {
            return true;
        }

        // Private albums require authentication
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can create albums.
     */
    public function create(?User $user): bool
    {
        // Admin, Ketua, Sekretaris can create albums
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can update the album.
     */
    public function update(?User $user, Album $album): bool
    {
        // Admin, Ketua, Sekretaris can update albums
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete the album.
     */
    public function delete(?User $user, Album $album): bool
    {
        // Admin and Ketua can delete albums
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    /**
     * Determine whether the user can upload photos to the album.
     */
    public function uploadPhotos(?User $user, Album $album): bool
    {
        // Admin, Ketua, Sekretaris can upload photos
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    /**
     * Determine whether the user can delete photos from the album.
     */
    public function deletePhotos(?User $user, Album $album): bool
    {
        // Admin, Ketua, Sekretaris can delete photos
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }
}
