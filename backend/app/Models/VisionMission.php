<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisionMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'vision',
        'missions',
        'period_start',
        'period_end',
        'status',
        'created_by',
    ];

    protected $casts = [
        'missions' => 'array',
        'period_start' => 'integer',
        'period_end' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function (VisionMission $vm) {
            $changed = $vm->getDirty();
            if (empty($changed)) {
                return;
            }
            $old = array_intersect_key($vm->getOriginal(), $changed);
            VisionMissionHistory::create([
                'vision_mission_id' => $vm->id,
                'changed_by' => auth()->id(),
                'action' => 'updated',
                'old_data' => $old,
                'new_data' => $changed,
            ]);
        });

        static::deleting(function (VisionMission $vm) {
            VisionMissionHistory::create([
                'vision_mission_id' => $vm->id,
                'changed_by' => auth()->id(),
                'action' => 'deleted',
                'old_data' => $vm->getOriginal(),
                'new_data' => null,
            ]);
        });
    }

    /**
     * Scope for active vision/mission
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive vision/mission
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for current period
     */
    public function scopeCurrentPeriod($query)
    {
        $currentYear = now()->year;
        return $query->where(function ($q) use ($currentYear) {
            $q->where('period_start', '<=', $currentYear)
                ->where(function ($q2) use ($currentYear) {
                    $q2->whereNull('period_end')
                        ->orWhere('period_end', '>=', $currentYear);
                });
        });
    }

    /**
     * Get the user who created this vision/mission
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(VisionMissionHistory::class)->latest();
    }

    /**
     * Get formatted period
     */
    public function getPeriodAttribute()
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start . ' - ' . $this->period_end;
        } elseif ($this->period_start) {
            return $this->period_start . ' - Sekarang';
        }
        return 'Tidak ditentukan';
    }

    /**
     * Check if this is the active vision/mission
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Get mission count
     */
    public function getMissionCountAttribute()
    {
        return is_array($this->missions) ? count($this->missions) : 0;
    }
}
