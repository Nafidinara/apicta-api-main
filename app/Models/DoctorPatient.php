<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPatient extends Model
{
    use HasFactory;

    protected $table = 'doctor_patients';
    protected $fillable = [
        'doctor_id',
        'patient_id'
    ];

    protected $casts = [
        'doctor_id' => 'string',
        'patient_id' => 'string'
    ];

}
