<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContributionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'period',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }
}
