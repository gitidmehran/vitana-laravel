<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Patients;
use App\Models\Questionaires;
use App\Models\User;
class SuperBillCodes extends Model
{
    use HasFactory,SoftDeletes;
     protected $table = 'superbill_codes';
   
    protected $fillable = ['question_id', 'codes', 'clinic_id', 'created_user', 'history'];
    public function questionData(){
        return $this->belongsTo(Questionaires::class,'question_id');   
    }
   /* public function patient(){
        return $this->belongsTo(Patients::class,'patient_id');
    }
    public function program(){
        return $this->belongsTo(Programs::class,'program_id');   
    }
    public function user(){
        return $this->belongsTo(User::class,'doctor_id');   
    }*/

}
