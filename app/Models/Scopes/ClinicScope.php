<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use App\Models\User;
use App\Models\ClinicUser;
use Auth;

class ClinicScope implements Scope
{
    
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::user()->role != "1") {
            $clinicIds = Auth::user()->clinic_id;
            $clinicIds = explode(',', $clinicIds);
            
            if(count($clinicIds) > 1){
                $builder->whereIn('clinic_id',$clinicIds);
            }else{
                $clinic_id = Auth::user()->clinic_id;
                $builder->where('clinic_id',$clinic_id);
            }
        }
    }
}
