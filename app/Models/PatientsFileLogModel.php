<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patients;
use App\Models\Patients12;
use App\Models\Insurances;

class PatientsFileLogModel extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "patients_file_log";
    protected $fillable = [
        'insurance_id',
        'gap_year',
        'clinic_id',
        'total_records',
        'file_name',
        'missingMemberID',
        'existingPatient',
        'newPatient',
        'lastNameIssue',
        'firstNameIssue',
        'dobIssue',
        'insuranceIssue',
        'genderIssue',
        'multipleIssue',

        'created_user',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class);
    }
    public function insuranceData()
    {
        return $this->belongsTo(Insurances::class,'insurance_id','id'); 
    
    }
}
