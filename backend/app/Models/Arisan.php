<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Arisan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function participants()
    {
        return $this->hasMany(ArisanParticipant::class);
    }

    public function draws()
    {
        return $this->hasMany(ArisanDraw::class);
    }

    public function winners()
    {
        return $this->hasMany(ArisanWinner::class);
    }

    public function payments()
    {
        return $this->hasMany(ArisanPayment::class);
    }
}
