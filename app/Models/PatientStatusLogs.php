<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patients;
use App\Models\InsuranceHistory;

class PatientStatusLogs extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "patient_status_log";
    protected $fillable = [
        'patient_id',
        'insurance_id',
        'doctor_id',
        'clinic_id',
        'status',
        'group',
        'patient_year',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class);
    }

    public function insuranceHistories()
    {
        return $this->hasMany(InsuranceHistory::class, 'patient_id', 'patient_id');
    }
}
