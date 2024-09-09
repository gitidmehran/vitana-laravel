<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Patients;
use App\Models\Insurances;

class InsuranceHistory extends Model
{
    protected $table = 'insurance_history';
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'patient_id',
        'insurance_id',
        'member_id',
        'insurance_status',
        'insurance_start_date',
        'insurance_end_date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }
    
    public function insurance()
    {
        return $this->belongsTo(Insurances::class, 'insurance_id', 'id');
    }
}
