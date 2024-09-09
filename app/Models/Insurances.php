<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;
use App\Models\Clinic;

use App\Models\Patients;
use App\Models\CareGaps;
use App\Models\HumanaCareGaps;

class Insurances extends Model
{
    use HasFactory,SoftDeletes, HasClinicScope;
    protected $fillable = ['name','short_name','type_id', 'provider', 'created_user','clinic_id'];

    
    public function clinic(){
        return $this->belongsTo(Clinic::class,'clinic_id','id');
    }

    public function careGap()
    {
        // Define the relationship between Insurance and CareGap
        // Assuming you have a column named `insurance_id` in the care_gape and humana_care_gape tables
        return $this->hasOne(CareGaps::class);
    }

    public function humanaCareGap()
    {
        // Define the relationship between Insurance and HumanaCareGap
        return $this->hasOne(HumanaCareGaps::class);
    }
    public function patients()
    {
        return $this->hasMany(Patients::class, 'insurance_id');
    }

    //I have three insurance each insurance has many patients how define the insurance that has largest number of patients 

	// public function patients1122()
    // {
    //     return $this->hasMany(Patients::class);
    // }
}
