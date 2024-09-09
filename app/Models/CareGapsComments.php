<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;

use App\Models\Patients;
use App\Models\Insurances;
use App\Models\Clinic;

use App\Models\Programs;
use App\Models\User;
use App\Models\CareGaps;

class CareGapsComments extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = "caregap_comments";
    protected $fillable = ['patient_id', 'insurance_id', 'caregap_id', 'comment', 'caregap_name', 'created_user'];

    public function userinfo()
	{
		return $this->belongsTo(User::class,'created_user');   
	}
    
    public function careGapsData()
    {
        return $this->belongsTo(CareGaps::class,'caregap_id');   
    }
//     public function careGapsCommentData()
//    {
//        return $this->hasMany(CareGapsDetails::class, 'caregap_id', 'id');
//    }
}

