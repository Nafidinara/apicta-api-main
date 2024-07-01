<?php

namespace App\Models;

use App\Traits\UUID;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Patient
 * @package App\Models
 * @mixin Eloquent
 */
class Patient extends Model
{
    use HasFactory, UUID;
    protected $table = 'patients';
    protected $fillable = [
        'user_id',
        'fullname',
        'address',
        'phone',
        'emergency_phone',
        'gender',
        'age',
        'device_id',
        'condition'
    ];
    protected $casts = [
        'id' => 'string'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function doctors(){
        return $this->belongsToMany(Doctor::class,'doctor_patients',
            'patient_id','doctor_id')->
        withTimestamps();
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function diagnoses(){
        return $this->hasMany(Diagnose::class,'patient_id','id');
    }

    public function pivot(){
        return $this->belongsToMany(Doctor::class,'doctor_patients',
            'patient_id','doctor_id')->
        withTimestamps();
    }
}
