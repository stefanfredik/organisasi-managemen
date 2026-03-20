<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRole;
    
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MEMBER => 'Anggota',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Non-Aktif',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }
    public function hasPermission(string $permission): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        $position = $this->member?->position?->code;
        if (!$position) {
            return false;
        }

        $permissions = Setting::getValue("role_permissions_{$position}", []);

        // Direct check
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Inheritance check: manage_X implies view_X
        if (str_starts_with($permission, 'view_')) {
            $managePermission = str_replace('view_', 'manage_', $permission);
            if (in_array($managePermission, $permissions)) {
                return true;
            }
        }

        return false;
    }

    public function getPositionAttribute(): ?string
    {
        return $this->member?->position?->name;
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }
}
