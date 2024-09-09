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


class CareGaps extends Model
{

   use HasFactory,SoftDeletes, HasClinicScope;

   protected $fillable = [
      'member_id',

      'bp_control_gap',
      'bp_control_gap_insurance',

      'patient_id',
      'doctor_id',
      
      'gap_year',

      'breast_cancer_gap',
      'breast_cancer_gap_insurance',

      'colorectal_cancer_gap',
      'colorectal_cancer_gap_insurance',

      'emergency_room_visits',

      'eye_exam_gap',
      'eye_exam_gap_insurance',

      'hba1c_gap',
      'hba1c_gap_insurance',

      'hba1c_poor_gap',
      'hba1c_poor_gap_insurance',

      'insurance_id',
      'clinic_id',

      'high_bp_gap',
      'high_bp_gap_insurance',

      'inpatient_admits',

      'kidney_health_gap',
      'kidney_health_gap_insurance',

      'member_vbp_type',
      'pcp_npi',
      'pcp_tax_id',
      'pcp_tax_id_name',
      'total_gaps',

      'awv_gap',
      'awv_gap_insurance',
      
      'adults_func_gap',
      'adults_func_gap_insurance',
      
      'post_disc_gap',
      'post_disc_gap_insurance',
      
      'adults_medic_gap',
      'adults_medic_gap_insurance',
      
      'after_inp_disc_gap',
      'after_inp_disc_gap_insurance',
      
      'pain_screening_gap',
      'pain_screening_gap_insurance',

      'depression_phq9_gap',
      'fall_screening_gap',
      'flu_vaccine_gap',
      'tobacco_use_gap',
      'cholesterol_assessment_gap',
      'source',
      'created_user',
      'q_id',
      'statin_therapy_gap',
      'osteoporosis_mgmt_gap'
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

   public function scopeThisYearGaps($query, $year = "") {
      if (!empty($year)) {
         return $query->where('gap_year', $year);
      } else {
         return $query->where('gap_year', now()->year);
      }
   }

}

