<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that the activity was performed on.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include activities for a specific model.
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('model_type', get_class($model))
            ->where('model_id', $model->id);
    }

    /**
     * Scope a query to only include activities of a specific type.
     */
    public function scopeOfType($query, string $action)
    {
        return $query->where('action', $action);
    }
}
