<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'member_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'amount',
        'donation_date',
        'is_anonymous',
        'notes',
        'receipt_path',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'donation_date' => 'date',
        'is_anonymous' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
