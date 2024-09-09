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

class HumanaCareGaps extends Model
{

   use HasFactory,SoftDeletes, HasClinicScope;
   protected $table = "humana_care_gaps";
   protected $fillable = [
      'member_id',
      'patient_id',
      'doctor_id',
      'insurance_id',
      'clinic_id',
      'gap_year',

      'breast_cancer_gap',
      'breast_cancer_gap_insurance',

      'colorectal_cancer_gap',
      'colorectal_cancer_gap_insurance',

      'high_bp_gap',
      'high_bp_gap_insurance',
      
      'eye_exam_gap',
      'eye_exam_gap_insurance',
      
      'faed_visit_gap',
      'faed_visit_gap_insurance',
      
      'hba1c_poor_gap',
      'hba1c_poor_gap_insurance',
      
      'omw_fracture_gap',
      'omw_fracture_gap_insurance',
      
      'pc_readmissions_gap',
      'pc_readmissions_gap_insurance',
      
      'spc_disease_gap',
      'spc_disease_gap_insurance',
         
      'post_disc_gap',
      'post_disc_gap_insurance',
         
      'after_inp_disc_gap',
      'after_inp_disc_gap_insurance',
      
      'ma_cholesterol_gap',
      'ma_cholesterol_gap_insurance',
      
      'mad_medications_gap',
      'mad_medications_gap_insurance',
      
      'ma_hypertension_gap',
      'ma_hypertension_gap_insurance',
      
      'sup_diabetes_gap',
      'sup_diabetes_gap_insurance',
      
      'awv_gap',
      'awv_gap_insurance',

      'source',
      'q_id',
      'created_user'
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
   public function patient()
	{
		return $this->belongsTo(Patients::class,'patient_id','id' );//->where('deleted_at', NULL);   
	}

   public function scopeThisYearGaps($query, $year = "") {
      if (!empty($year)) {
         return $query->where('gap_year', $year);
      } else {
         return $query->where('gap_year', now()->year);
      }
   }
}

