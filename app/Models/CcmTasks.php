<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Questionaires;
use App\Models\CcmMonthlyAssessment;

class CcmTasks extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $fillable = ['monthly_encounter_id', 'annual_encounter_id', 'ccm_cordinator_id', 'date_of_service', 'task_type', 'task_date', 'task_time'];

    /**
     * Scope a query to only include records based on user role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $roleId
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByUserRole($query, $roleId, $userId)
    {
        if ($roleId == 1 || $roleId == 24) {
            return $query;
        } else {
            return $query->where('ccm_cordinator_id', $userId);
        }
    }

    public function annualAssessment()
    {
        return $this->belongsTo(Questionaires::class,'annual_encounter_id','id');
    }

    public function monthlyAssessment()
    {
        return $this->belongsTo(CcmMonthlyAssessment::class,'monthly_encounter_id','id');
    }
    
    public function coordinators()
    {
        return $this->belongsTo(User::class,'ccm_cordinator_id','id');
    }
}
