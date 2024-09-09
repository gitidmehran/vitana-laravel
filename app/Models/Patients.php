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
use App\Models\CareGaps;
use App\Models\HumanaCareGaps;
use App\Models\MedicareArizonaCareGaps;
use App\Models\AetnaMedicareCareGaps;
use App\Models\AllwellMedicareCareGaps;
use App\Models\HealthchoiceArizonaCareGaps;
use App\Models\UnitedHealthcareCareGaps;
use App\Models\CareGapsDetails;
use App\Models\Clinic;
use App\Models\PatientStatusLogs;
use App\Models\PatientsChangesHistoryModel;
use App\Models\InsuranceHistory;



class Patients extends Model
{

	use HasFactory,SoftDeletes, HasClinicScope;

	protected $appends = ['name'];

	protected $fillable = [
		'identity',
		'unique_id',
		'member_id',
		'last_name',
		'first_name',
		'mid_name',
		'doctor_id',
		'insurance_id',
		'contact_no',
		'cell',
		'dob',
		'age',
		'gender',
		'address',
		'address_2',
		'city',
		'state',
		'zipCode',
		'email',
		'dod',
		'family_history',
		'social_history',
		'disease',
		'created_user',
		'change_doctor_id',
		'change_address',
		'clinic_id',
		'patient_consent',
		'consent_data',
		'group',
		'status',
		'patient_year',
		'preferred_contact',
		'coordinator_id'
	];

	public function insurance123() 
	{
        // Define the relationship between Patient and Insurance
        // Assuming you have a column named `insurance_id` in the patients table
        return $this->belongsTo(Insurances::class);
    }

	public function getNameAttribute()
	{
		//return $this->first_name. ' ' .$this->mid_name.' '.$this->last_name;
		return $this->last_name. ' ' .$this->mid_name.' '.$this->first_name;
	}

	public function doctor()
	{
		return $this->belongsTo(User::class,'doctor_id','id');   
	}
	public function userinfo()
	{
		return $this->belongsTo(User::class,'created_user','id');   
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

	public function patients_history(){

		return $this->hasmany(PatientsChangesHistoryModel::class,'patient_id','id')
		->select(['id','patient_id','differences','created_user','updated_at'])
		->orderByDesc('id');    
	}

	public function insurance()
	{
		return $this->belongsTo(Insurances::class,'insurance_id','id');   
	}

	public function insuranceHistories()
    {
        return $this->hasMany(InsuranceHistory::class,'patient_id','id');
    }

	public function clinic()
	{
		return $this->belongsTo(Clinic::class,'clinic_id','id');   
	}

	// public function questionServey(){
	//    return $this->hasOne(Questionaires::class,'id','patient_id')->where('program_id', '1'); 
	// }

	/**
		* Get the awvEncounter associated with the Patients
		*
		* @return \Illuminate\Database\Eloquent\Relations\HasOne
		*/
	public function awvEncounter()
	{
		return $this->hasOne(Questionaires::class, 'patient_id', 'id')->where('program_id', '1');
	}

	public function question()
	{
		return $this->hasMany(Questionaires::class, 'patient_id', 'id');
	}

	public function careGapsData() {
		return $this->hasOne(CareGaps::class,'patient_id','id');
	}
	
	public function careGapsDataHumana(){
		return $this->hasOne(HumanaCareGaps::class,'patient_id','id');
	}

	public function careGapsDataMedicareArizona(){
		return $this->hasOne(MedicareArizonaCareGaps::class,'patient_id','id');
	}
	
	public function careGapsDataAetnaMedicare(){
		return $this->hasOne(AetnaMedicareCareGaps::class,'patient_id','id');
	}

	public function careGapsDataAllwellMedicare(){
		return $this->hasOne(AllwellMedicareCareGaps::class,'patient_id','id');
	}

	public function careGapsDataHealthchoiceArizona(){
		return $this->hasOne(HealthchoiceArizonaCareGaps::class,'patient_id','id');
	}

	public function careGapsDataUnitedHealthcare(){
		return $this->hasOne(UnitedHealthcareCareGaps::class,'patient_id','id');
	}
	
	public function careGapsCurrentMedicareArizona(){
		return $this->hasOne(MedicareArizonaCareGaps::class,'patient_id','id')
		->select(
			[
				'id',
				'member_id',
				'patient_id',
				'doctor_id',
				'insurance_id',
				'breast_cancer_gap',
				'colorectal_cancer_gap',
				'high_bp_gap',
				'hba1c_gap',
				'awv_gap',
			]
		); 

	}
	
	public function careGapsCurrentHumana(){
		return $this->hasOne(HumanaCareGaps::class,'patient_id','id')
		->select(
			[
				'id',
				'member_id',
				'patient_id',
				'doctor_id',
				'insurance_id',
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
			]
		); 

	}

	public function careGapsCurrent(){

		return $this->hasOne(CareGaps::class,'patient_id','id')
		->select(
			[
				'id',
				'member_id',
				'patient_id',
				'doctor_id',
				'insurance_id',
				'breast_cancer_gap',
				'colorectal_cancer_gap',
				'eye_exam_gap',
				'hba1c_poor_gap',
				'high_bp_gap',
				'statin_therapy_gap',
				'osteoporosis_mgmt_gap',
				'adults_medic_gap',
				'pain_screening_gap',
				'post_disc_gap',
				'adults_func_gap',
				'after_inp_disc_gap',
				'awv_gap',
			]
		); 

	}
	
	public function careGapsCommentData(){
		// return $this->hasOne(CareGapsComments::class,'patient_id','id');
		return $this->hasMany(CareGapsComments::class,'patient_id','id');

	}


	/**
		* Get all of the GAPS DETAILS for the Patients
		*
		* @return \Illuminate\Database\Eloquent\Relations\HasMany
		*/
	public function CareGapsDetails()
	{
		return $this->hasMany(CareGapsDetails::class, 'patient_id', 'id');
	}

	public function scopeOfCoordinatorID($query, $bulkAssign="") {
		if ($bulkAssign == 1) {
			$query->whereNull('coordinator_id');
		}
	}
	
    //I have three insurance each insurance has many patients how define the insurance that has largest number of patients 

	// public function insurance1122()
    // {
    //     return $this->belongsTo(Insurances::class);
    // }

	public function CareGaps()
	{
    	return $this->hasMany(CareGaps::class, 'patient_id','id');
	}
	public function HumanaCareGaps()
	{
    	return $this->hasMany(HumanaCareGaps::class, 'patient_id','id');
	}
	public function MedicareArizonaCareGaps()
	{
    	return $this->hasMany(MedicareArizonaCareGaps::class, 'patient_id','id');
	}
	public function AetnaMedicareCareGaps()
	{
    	return $this->hasMany(AetnaMedicareCareGaps::class, 'patient_id','id');
	}
	public function AllwellMedicareCareGaps()
	{
    	return $this->hasMany(AllwellMedicareCareGaps::class, 'patient_id','id');
	}
	public function HealthchoiceArizonaCareGaps()
	{
    	return $this->hasMany(HealthchoiceArizonaCareGaps::class, 'patient_id','id');
	}
	public function UnitedHealthcareCareGaps()
	{
    	return $this->hasMany(UnitedHealthcareCareGaps::class, 'patient_id','id');
	}

	/**
	 * Get all of the StatusLogs for the Patients
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function statusLogs()
	{
		return $this->hasMany(PatientStatusLogs::class, 'patient_id', 'id');
	}
}

