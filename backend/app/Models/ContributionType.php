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
        'wallet_id',
        'amount',
        'period',
        'description',
        'is_active',
        'due_date',
        'start_date',
        'end_date',
        'recurring_day',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'due_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
