<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user instanceof \App\Models\User) {
            $this->activityLogger->logLogout($user);
        }
    }
}
