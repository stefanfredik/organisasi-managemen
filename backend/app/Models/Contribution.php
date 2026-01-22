<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'contribution_type_id',
        'wallet_id',
        'amount',
        'payment_date',
        'payment_period',
        'payment_method',
        'status',
        'receipt_path',
        'notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class, 'contribution_type_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
