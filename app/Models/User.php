<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\UUID;
use Spatie\Permission\Traits\HasRoles;
use Eloquent;

/**
 * Class User
 * @package App\Models
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UUID, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $guard_name = 'api';

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function role_data()
    {
        if (Auth::user()->hasRole('doctor')) {
            return $this->hasOne(Doctor::class);
        } elseif (Auth::user()->hasRole('patient')) {
            return $this->hasOne(Patient::class);
        }
    }

    //    public function admin() {
    //    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }
}
