<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

use App\Models\Questionaires;
use App\Models\SurgicalHistory;
use App\Models\Diagnosis;

use Auth;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "clinics";
    protected $fillable = [
        'name',
        'short_name',
        'phone',
        'contact_no',
        'address',
        'address_2',
        'city',
        'state',
        'zip_code',
        'created_user'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted(){
        $role = Auth::user()->role;
        $clinicId = Auth::user()->clinic_id;
        static::addGlobalScope(function(Builder $builder) use ($role,$clinicId){
            if($role!="1"){
                $builder->whereIn('id',explode(",", $clinicId));
            }
        });
    }

}
