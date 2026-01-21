<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'publish_date',
        'status',
        'target_audience',
        'target_roles',
        'created_by',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'target_roles' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

