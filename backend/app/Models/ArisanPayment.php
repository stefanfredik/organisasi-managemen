<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Arisan;
use App\Models\Member;

class ArisanPayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function arisan()
    {
        return $this->belongsTo(Arisan::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
