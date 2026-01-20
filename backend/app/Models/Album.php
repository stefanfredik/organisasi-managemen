<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Album extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'cover_image',
        'event_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for public albums
     */
    public function scopePublic($query)
    {
        return $query->where('status', 'public');
    }

    /**
     * Scope for private albums
     */
    public function scopePrivate($query)
    {
        return $query->where('status', 'private');
    }

    /**
     * Scope for albums by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->name) . '-' . Str::random(5);
            }
        });
    }

    /**
     * Get the event associated with the album
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who created the album
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all photos in the album
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('order');
    }

    /**
     * Get the count of photos in the album
     */
    public function getPhotosCountAttribute()
    {
        return $this->photos()->count();
    }
}
