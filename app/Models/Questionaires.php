<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;

use App\Models\Patients;
use App\Models\Clinic;
use App\Models\Programs;
use App\Models\User;
use App\Models\SuperBillCodes;
use App\Models\CcmMonthlyAssessment;
use App\Models\Insurances;
use App\Models\CcmTasks;

class Questionaires extends Model
{
    use HasFactory,SoftDeletes, HasClinicScope;
    protected $casts = ['questions_answers','array'];
    protected $fillable = ['patient_id', 'program_id', 'clinic_id', 'insurance_id', 'serial_no', 'questions_answers', 'date_of_service', 'created_user', 'doctor_id', 'coordinator_id', 'status_id','status'];

    public function patient()
    {
        return $this->belongsTo(Patients::class,'patient_id');
    }

    public function program()
    {
        return $this->belongsTo(Programs::class,'program_id');   
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class,'clinic_id');   
    }

    public function user()
    {
        return $this->belongsTo(User::class,'doctor_id');   
    }

    public function superBill()
    {
        return $this->belongsTo(SuperBillCodes::class,'id','question_id');
    }

    public function monthlyAssessment()
    {
        return $this->hasOne(CcmMonthlyAssessment::class,'questionnaire_id','id')->latest();
    }
    
    public function allMonthlyAssessment()
    {
        return $this->hasMany(CcmMonthlyAssessment::class,'questionnaire_id','id');
    }

    public function ccmTasks()
    {
        return $this->hasMany(CcmTasks::class,'annual_encounter_id','id');
    }
}

