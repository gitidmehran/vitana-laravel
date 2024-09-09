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

class UnitedHealthcareCareGaps extends Model
{

      use HasFactory,SoftDeletes, HasClinicScope;
      protected $table = "uhc_care_gaps";
      protected $fillable = [
            
      'member_id',
      'patient_id',
      'doctor_id',
      'insurance_id',
      'clinic_id',
      'gap_year',
      //United Health Care
      //image 1
      //ExcelFile 1 ok
      //C01-Breast Cancer Screening
      'breast_cancer_gap',
      'breast_cancer_gap_insurance',

      //image 2
      //ExcelFile 2 ok
      //C02-Colorectal Cancer Screening 
      'colorectal_cancer_gap',
      'colorectal_cancer_gap_insurance',

      //image 3
      //ExcelFile 3 ok
      //C06-Care for Older Adults - Medication Review
      'adults_medic_gap',
      'adults_medic_gap_insurance',

      //image 4
      //ExcelFile 4 ok
      //DMC10-Care for Older Adults - Functional Status Assessment .
      'adults_fun_status_gap',
      'adults_fun_status_gap_insurance',

      //image 5
      //ExcelFile 5 ok
      //C07-Care for Older Adults - Pain Assessment
      'pain_screening_gap',
      'pain_screening_gap_insurance',

      //image 6
      //ExcelFile 6 ok
      //C09-Eye Exam for Patients With Diabetes
      'eye_exam_gap',
      'eye_exam_gap_insurance',

      //image 7
      //ExcelFile 7 ok
      //C10-Kidney Health Evaluation for Patients With Diabetes
      'kidney_health_diabetes_gap',
      'kidney_health_diabetes_gap_insurance',

      //image 8
      //ExcelFile 8 ok
      //C11-Hemoglobin A1c Control for Patients With Diabetes
      'hba1c_gap',
      'hba1c_gap_insurance',

      //image 9
      //ExcelFile 9 ok
      //C12 - Controlling Blood Pressure
      'high_bp_gap',
      'high_bp_gap_insurance',

      //image 10
      //ExcelFile 10 ok
      //C16 - Statin Therapy for Patients with Cardiovascular Disease
      'statin_therapy_gap',
      'statin_therapy_gap_insurance',

      //image 11
      //ExcelFile 11 ok
      //D08-Med Ad. For Diabetes Meds Current Year Status
      'med_adherence_diabetes_gap',
      'med_adherence_diabetes_gap_insurance',

      //image 12
      //ExcelFile 12 ok
      //D09-Med Ad. (RAS antagonists) Current Year Statu
      'med_adherence_ras_gap',
      'med_adherence_ras_gap_insurance',

      //image 13
      //ExcelFile 13 ok
      //D10-Med Ad. (Statins) Current Year Status
      'med_adherence_statins_gap',
      'med_adherence_statins_gap_insurance',

      //image 14
      //ExcelFile 14 ok
      //D11-MTM CMR Current Year Status
      'mtm_cmr_gap',
      'mtm_cmr_gap_insurance',

      //image 15
      //ExcelFile 15 ok
      //D12-Statin Use in Persons with Diabetes Current Year Status
      'sup_diabetes_gap',
      'sup_diabetes_gap_insurance',

      //image 16
      //ExcelFile 16
      //AWV 
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

