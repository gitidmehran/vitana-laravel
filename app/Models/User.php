<?php

namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ClinicUser;
use App\Models\Clinic;
use Auth;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $appends = ['name'];
    protected $fillable = [
        'physician_npi_num',
        'first_name',
        'mid_name',
        'last_name',
        'email',
        'password',
        'contact_no',
        'role',
        'degree',
        'age',
        'gender',
        'address',
        'clinic_id',
        'program_id',
        'speciality',
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

    public function getNameAttribute()
    {
			$degree = !empty($this->degree) ? ', '.$this->degree : "";
      return $this->first_name.' '.$this->last_name.$degree;
    }

    public function clinicUser()
		{
      return $this->belongsTo(ClinicUser::class,'id','user_id');
    }

    public function clinic()
		{
			return $this->belongsTo(Clinic::class,'clinic_id','id');
    }
    
    public function tasks()
    {
			return $this->hasMany(CcmTasks::class,'ccm_cordinator_id','id');
    }

    public function scopeOfClinicID($query, $clinicId="") {
        $clinicIds = !empty($clinicId) ? $clinicId : Auth::user()->clinic_id;
        $clinicIds = gettype($clinicId) == "array" ? implode(",", $clinicIds) : $clinicIds;

        $query->where('clinic_id', "like", "%".$clinicIds."%")->where('role', '>', 1);
    }
}
