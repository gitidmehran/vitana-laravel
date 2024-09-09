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


class AllwellMedicareCareGaps extends Model
{

      use HasFactory,SoftDeletes, HasClinicScope;
      protected $table = "allwell_medicare_care_gaps";
      protected $fillable = [
            
      'member_id',
      'patient_id',
      'doctor_id',
      'insurance_id',
      'clinic_id',
      'gap_year',
      // Allwell

      //image 1
      //ExcelFile 2 
      //BCS - Breast Cancer Screening
      'breast_cancer_gap',
      'breast_cancer_gap_insurance',

      //image 2
      //ExcelFile 1 
      //CBP - Controlling High Blood Pressure
      'high_bp_gap',
      'high_bp_gap_insurance',

      //image 3
      //ExcelFile 6
      //CDC - Diabetes - Dilated Eye Exam
      'eye_exam_gap',
      'eye_exam_gap_insurance',

      //image 4
      //ExcelFile 7
      //CDC - Diabetes HbA1c <= 9
      'hba1c_gap',
      'hba1c_gap_insurance',

      //image 5
      //ExcelFile 9
      //COA - Care for Older Adults - Pain Assessment
      'pain_screening_gap',
      'pain_screening_gap_insurance',

      //image 6
      //ExcelFile 10
      //COA - Care for Older Adults - Review
      'adults_medic_gap',
      'adults_medic_gap_insurance',

      //image 7
      //ExcelFile 3
      //COL - Colorectal Cancer Screen (50 - 75 yrs)
      'colorectal_cancer_gap',
      'colorectal_cancer_gap_insurance',

      //image 8 new
      //ExcelFile 11
      //FMC - F/U ED Multiple High Risk Chronic Conditions
      'm_high_risk_cc_gap',
      'm_high_risk_cc_gap_insurance',

      //image 9 new 
      //ExcelFile 14
      //Med Adherence - Diabetic
      'med_adherence_diabetic_gap',
      'med_adherence_diabetic_gap_insurance',

      //image 10 new
      //ExcelFile 4
      //Med Adherence - RAS
      'med_adherence_ras_gap',
      'med_adherence_ras_gap_insurance',

      //image 11 new
      //ExcelFile 5
      //Med Adherence - Statins
      'med_adherence_statins_gap',
      'med_adherence_statins_gap_insurance',

      //image 12 new 
      //ExcelFile 16
      //SPC - Statin Therapy for Patients with CVD
      'spc_statin_therapy_cvd_gap',
      'spc_statin_therapy_cvd_gap_insurance',

      //image 13
      //ExcelFile 8
      //SUPD - Statin Use in Persons With Diabetes
      'sup_diabetes_gap',
      'sup_diabetes_gap_insurance',

      //image 14 new
      //ExcelFile 12
      //TRC - Engagement After Discharge
      'trc_eng_after_disc_gap',
      'trc_eng_after_disc_gap_insurance',

      //image 15 new
      //ExcelFile 13
      //TRC - Med Reconciliation Post Discharge
      'trc_mr_post_disc_gap',
      'trc_mr_post_disc_gap_insurance',

      //image no show new 
      //ExcelFile 15
      'kidney_health_diabetes_gap',
      'kidney_health_diabetes_gap_insurance',

      //image no show
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

