<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Questionaires;
use App\Models\CcmTasks;
use App\Models\Patients;

class CcmMonthlyAssessment extends Model
{
    use HasFactory,SoftDeletes;
    protected $casts = ['monthly_assessment','array'];

    protected $fillable = ['questionnaire_id', 'serial_no', 'patient_id', 'coordinator_id', 'clinic_id', 'program_id', 'date_of_service', 'monthly_assessment', 'status'];

    public function monthlyAssessment()
    {
        return $this->belongsTo(Questionaires::class,'questionnaire_id','id')->latest();
    }

    /**
     * This PHP function returns a collection of CcmTasks objects that belong to a MonthlyEncounter
     * object.
     * 
     * @return A hasMany relationship between the current model and the CcmTasks model, where the
     * foreign key 'monthly_encounter_id' in the CcmTasks table references the 'id' field in the
     * current model's table.
     */
    public function ccmTasks()
    {
        return $this->hasMany(CcmTasks::class,'monthly_encounter_id','id');
    }

    /**
     * This PHP function returns a relationship between the current object and a Patients object based
     * on the patient_id and id fields.
     * 
     * @return The function `patient()` is returning a `belongsTo` relationship between the current
     * model and the `Patients` model, where the foreign key is `patient_id` and the local key is `id`.
     */
    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }
}
