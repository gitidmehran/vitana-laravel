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
use App\Models\HumanaCareGaps;

class CareGapsDetails extends Model
{
    use HasFactory,SoftDeletes, HasClinicScope;
    protected $table = "caregap_details";
    protected $casts = ['gap_details','array'];
    protected $fillable = ['patient_id', 'insurance_id', 'clinic_id', 'status', 'caregap_id', 'caregap_details', 'caregap_name', 'filename', 'created_user'];

    public function patient()
    {
        return $this->belongsTo(Patients::class,'patient_id');
    }
    public function careGapsData()
    {
        return $this->belongsTo(CareGaps::class,'caregap_id');   
    }
    public function userinfo()
	{
		return $this->belongsTo(User::class,'created_user');   
	}

    public function scopeThisYearGaps($query, $year)
    {
        if (!empty($year)) {
            return $query->whereRaw("JSON_EXTRACT(caregap_details, '$.date') LIKE ?", ["%$year%"]);
        } else {
            $year = now()->year;
            return $query->whereRaw("JSON_EXTRACT(caregap_details, '$.date') LIKE ?", ["%$year%"]);
        }
    }
}

