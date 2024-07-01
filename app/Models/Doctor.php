<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Eloquent;

/**
 * Class Doctor
 * @package App\Models
 * @mixin Eloquent
 */

class Doctor extends Model
{
    use HasFactory, UUID;

    protected $table = 'doctors';
    protected $fillable = ['user_id', 'fullname', 'address', 'phone', 'emergency_phone', 'gender', 'age'];

    protected $casts = [
        'id' => 'string'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function patients(){
        return $this->belongsToMany(Patient::class,'doctor_patients',
            'doctor_id','patient_id')->
            withTimestamps();
    }

    public function pivot(){
        return $this->belongsToMany(Patient::class,'doctor_patients',
            'doctor_id','patient_id')->
        withTimestamps();
    }
}
