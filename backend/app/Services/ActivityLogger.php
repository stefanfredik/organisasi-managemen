<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * @param string $action
     * @param string $description
     * @param array $properties
     * @param Model|null $model
     * @param User|null $user
     * @return ActivityLog
     */
    public function log(
        string $action,
        string $description,
        array $properties = [],
        ?Model $model = null,
        ?User $user = null
    ): ActivityLog {
        $user = $user ?? auth()->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log user login activity.
     *
     * @param User $user
     * @return ActivityLog
     */
    public function logLogin(User $user): ActivityLog
    {
        return $this->log(
            action: 'login',
            description: "User {$user->name} logged in",
            properties: [
                'email' => $user->email,
                'role' => $user->role,
            ],
            user: $user
        );
    }

    /**
     * Log user logout activity.
     *
     * @param User $user
     * @return ActivityLog
     */
    public function logLogout(User $user): ActivityLog
    {
        return $this->log(
            action: 'logout',
            description: "User {$user->name} logged out",
            properties: [
                'email' => $user->email,
            ],
            user: $user
        );
    }

    /**
     * Log model creation.
     *
     * @param Model $model
     * @param string $description
     * @param array $properties
     * @return ActivityLog
     */
    public function logCreate(Model $model, string $description, array $properties = []): ActivityLog
    {
        return $this->log(
            action: 'create',
            description: $description,
            properties: array_merge([
                'model_class' => class_basename($model),
                'model_id' => $model->id,
            ], $properties),
            model: $model
        );
    }

    /**
     * Log model update.
     *
     * @param Model $model
     * @param string $description
     * @param array $changes
     * @return ActivityLog
     */
    public function logUpdate(Model $model, string $description, array $changes = []): ActivityLog
    {
        return $this->log(
            action: 'update',
            description: $description,
            properties: array_merge([
                'model_class' => class_basename($model),
                'model_id' => $model->id,
                'changes' => $changes,
            ]),
            model: $model
        );
    }

    /**
     * Log model deletion.
     *
     * @param Model $model
     * @param string $description
     * @param array $properties
     * @return ActivityLog
     */
    public function logDelete(Model $model, string $description, array $properties = []): ActivityLog
    {
        return $this->log(
            action: 'delete',
            description: $description,
            properties: array_merge([
                'model_class' => class_basename($model),
                'model_id' => $model->id,
            ], $properties),
            model: $model
        );
    }

    /**
     * Log custom action.
     *
     * @param string $action
     * @param string $description
     * @param array $properties
     * @return ActivityLog
     */
    public function logCustom(string $action, string $description, array $properties = []): ActivityLog
    {
        return $this->log(
            action: $action,
            description: $description,
            properties: $properties
        );
    }
}
