<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionaires;
class Schedule extends Model
{
    use HasFactory, SoftDeletes;
   protected $fillable = ['patient_id','doctor_id','clinic_id','insurance_id','status','confirmation','last_visit','scheduled_date','scheduled_time'];
    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
    public function last_visit()
    {
        return $this->belongsTo(Questionaires::class,'patient_id','id');
    }
}