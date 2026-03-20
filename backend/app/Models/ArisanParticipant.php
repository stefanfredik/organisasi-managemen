<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Arisan;
use App\Models\Member;

class ArisanParticipant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'joined_at' => 'datetime',
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
