<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Arisan;
use App\Models\ArisanWinner;

class ArisanDraw extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'drawn_at' => 'datetime',
    ];

    public function arisan()
    {
        return $this->belongsTo(Arisan::class);
    }

    public function winners()
    {
        return $this->hasMany(ArisanWinner::class);
    }
}
