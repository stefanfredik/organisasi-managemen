<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user instanceof \App\Models\User) {
            $this->activityLogger->logLogin($user);

            // Update last login timestamp
            $user->update([
                'last_login_at' => now(),
            ]);
        }
    }
}
