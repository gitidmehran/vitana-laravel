<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;

use App\Models\User;
use App\Models\Insurances;
use App\Models\Questionaires;
use App\Models\SurgicalHistory;
use App\Models\Diagnosis;
use App\Models\Medications;
use App\Models\Patients;
use App\Models\CareGapsDetails;
use App\Models\CareGapsComments;


class HealthchoiceArizonaCareGaps extends Model
{

      use HasFactory,SoftDeletes, HasClinicScope;
      protected $table = "healthchoice_arizona_care_gaps";
      protected $fillable = [
            
      'member_id',
      'patient_id',
      'doctor_id',
      'insurance_id',
      'clinic_id',
      'gap_year',
      // Health Choice Arizona

      //image 1
      //ExcelFile 1
      //BCS - Breast Cancer Screening
      'breast_cancer_gap',
      'breast_cancer_gap_insurance',

      //image 2
      //ExcelFile 2 
      //CCS - Cervical Cancer Screening
      'cervical_cancer_gap',
      'cervical_cancer_gap_insurance',

      //image 3
      //ExcelFile 3 
      //HDO - Use of Opioids at High Dosage
      'opioids_high_dosage_gap',
      'opioids_high_dosage_gap_insurance',

      //image 4
      //ExcelFile 4
      //HBD - Hemoglobin A1c (HbA1c) Poor Control >9%
      'hba1c_poor_gap',
      'hba1c_poor_gap_insurance',

      //image 5
      //ExcelFile 5
      //PPC1 - Timeliness of Prenatal Care
      'ppc1_gap',
      'ppc1_gap_insurance',

      //image 6
      //ExcelFile 6
      //PPC2 - Timeliness of Prenatal Care
      'ppc2_gap',
      'ppc2_gap_insurance',

      //image 7
      //ExcelFile 7
      //WCV - Well-Child Visits for Age 3-21
      'well_child_visits_gap',
      'well_child_visits_gap_insurance',

      //image 8
      //ExcelFile 8
      //Chlamydia Screening
      'chlamydia_gap',
      'chlamydia_gap_insurance',

      //image 9
      //ExcelFile 9 
      //CBP - Controlling High Blood Pressure
      'high_bp_gap',
      'high_bp_gap_insurance',

      //image 10
      //ExcelFile 10
      //Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)
      'fuh_30Day_gap',
      'fuh_30Day_gap_insurance',

      //image 11
      //ExcelFile 11
      //Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)
      'fuh_7Day_gap',
      'fuh_7Day_gap_insurance',

      //image 12
      //ExcelFile 12
      //HBD - Hemoglobin A1c (HbA1c) Poor Control >9%
      'awv_gap',
      'awv_gap_insurance',

      'source',
      'q_id',
      'created_user',
      'created',
      'created_at',
      'updated_at',
      'deleted_at'
      ];

 
      public function doctor()
      {
            return $this->belongsTo(User::class,'doctor_id','id');   
      }

      public function coordinator()
      {
            return $this->belongsTo(User::class,'coordinator_id','id');   
      }

      public function diagnosis()
      {
            return $this->hasmany(Diagnosis::class,'patient_id','id');   
      }

      public function medication()
      {
            return $this->hasmany(Medications::class,'patient_id','id');   
      }

      public function surgical_history()
      {
            return $this->hasmany(SurgicalHistory::class,'patient_id','id',);   
      }

      public function insurance()
      {
            return $this->belongsTo(Insurances::class,'insurance_id','id');   
      }

      public function questionServey() 
      {
            return $this->belongsTo(Questionaires::class,'question_id','id');   
      }

      /**
       * Get all of the comments for the CareGaps
      *
      * @return \Illuminate\Database\Eloquent\Relations\HasMany
      */
      public function caregapsDetails()
      {
            return $this->hasMany(CareGapsDetails::class, 'caregap_id', 'id');
      }
      public function caregapsComments()
      {
            return $this->hasMany(CareGapsComments::class, 'caregap_id', 'id');
      }
      public function scopeOfCoordinatorID($query, $bulkAssign="") {
            if ($bulkAssign == 1) {
            $query->whereNull('coordinator_id');
            }
      }
	  
	public function patientinfo()
	{
		return $this->belongsTo(Patients::class,'patient_id');//->where('deleted_at', NULL);   
	}

	public function scopeThisYearGaps($query, $year = "") 
	{
		if (!empty($year)) {
			return $query->where('gap_year', $year);
		} else {
			return $query->where('gap_year', now()->year);
		}
	}

}