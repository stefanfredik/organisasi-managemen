<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    public function view(?User $user, Document $document): bool
    {
        return $this->isAuthenticated($user);
    }

    public function create(?User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function update(?User $user, Document $document): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua', 'sekretaris']);
    }

    public function delete(?User $user, Document $document): bool
    {
        return $this->hasAnyRole($user, ['admin', 'ketua']);
    }

    public function download(?User $user, Document $document): bool
    {
        return $this->isAuthenticated($user);
    }
}
