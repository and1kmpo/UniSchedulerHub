<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function professor()
    {
        return $this->hasOne(Professor::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }


    const STATUS_ACTIVE = '1';
    const STATUS_INACTIVE = '2';

    //Scope para filtrar usuarios activos
    public function ScopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }


    //Scope para filtrar usuarios inactivos
    public function ScopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function activate()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function deactivate()
    {
        $this->update(['status' => self::STATUS_INACTIVE]);
    }
}
