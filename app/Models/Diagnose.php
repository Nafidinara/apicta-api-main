<?php

namespace App\Models;

use App\Traits\UUID;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Diagnose
 * @package App\Models
 * @mixin Eloquent
 */

class Diagnose extends Model
{
    use HasFactory, UUID;
    protected $table = 'diagnoses';
    protected $fillable = ['patient_id', 'doctor_id', 'diagnoses','notes','is_verified','file'];

    public function doctor() {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function patient() {
        return $this->belongsTo(Patient::class);
    }
}
