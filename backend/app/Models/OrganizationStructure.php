<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'position_name',
        'level',
        'parent_id',
        'sort_order',
        'period_start',
        'period_end',
        'is_active',
        'photo_path',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'period_start' => 'integer',
        'period_end' => 'integer',
        'is_active' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationStructure::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationStructure::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
