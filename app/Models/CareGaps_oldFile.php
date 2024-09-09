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


class CareGaps extends Model
{

   use HasFactory,SoftDeletes, HasClinicScope;

   protected $appends = ['name'];

   protected $fillable = [
      'member_id',
      'high_risk',
      'patient_id',
      'doctor_id',
      'last_office_visit_Assigned_TIN',
      'last_office_visit_Any_TIN',
      'ed_visit',
      'ip_visit',
      'hcc',
      'che',
      'iha',
      'bcs',
      'khe',
      'hab1c9',
      'hab1c8',
      'bpc',

      'insurance_id',
      'clinic_id',
      'cbp',
      'coa3',
      'coa2',
      'coa4',
      'col',
      'cdc2',
      'cdc4',
      'trce',
      'trcm',
      'created_user'
   ];


   public function getNameAttribute()
   {
      return $this->first_name. ' ' .$this->mid_name.' '.$this->last_name;
   }

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

   public function questionServey(){

      return $this->belongsTo(Questionaires::class,'question_id','id');   

   }

   public function scopeOfCoordinatorID($query, $bulkAssign="") {
      if ($bulkAssign == 1) {
         $query->whereNull('coordinator_id');
      }
   }  

}

