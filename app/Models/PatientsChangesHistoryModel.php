<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patients;
use App\Models\User;
use App\Models\Insurances;

class PatientsChangesHistoryModel extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "patients_change_history";
    protected $fillable = [
        'patient_id',
        'insurance_id',
        'differences',
        'source',
        // 'previous',
        // 'current',
        'created_user',
    ];
    public function patient123()
    {
        return $this->belongsTo(Patients::class);
    }
    public function patient() {
		return $this->hasOne(Patients::class,'patient_id','id');
	}
    public function userinfo()
	{
		return $this->belongsTo(User::class,'created_user');   
	}
    
    public function insurance()
{
    return $this->belongsTo(Insurances::class, 'insurance_id');
}
}
