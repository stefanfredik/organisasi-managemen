<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_date',
        'agenda',
        'participants',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'participants' => 'array',
    ];

    public function attachments()
    {
        return $this->hasMany(MeetingMinuteAttachment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
