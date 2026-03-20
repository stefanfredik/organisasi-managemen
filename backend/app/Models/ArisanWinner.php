<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArisanWinner extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function arisanDraw()
    {
        return $this->belongsTo(ArisanDraw::class);
    }

    public function arisan()
    {
        return $this->belongsTo(Arisan::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
