<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinuteAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_minute_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function meetingMinute()
    {
        return $this->belongsTo(MeetingMinute::class);
    }
}

