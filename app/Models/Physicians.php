<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Insurances;

class Physicians extends Model
{
   use HasFactory,SoftDeletes;
   protected $appends = ['name'];
   protected $fillable = [
        'first_name',
        'mid_name',
        'last_name',
        'contact_no',
        'role',
        'age',
        'gender',
        'address',
        'speciality',
        'created_user'
   ];

   public function getNameAttribute()
    {
       return $this->first_name. ' ' .$this->mid_name.' '.$this->last_name;
    }

   public function Physicians(){
      return $this->belongsTo(physicians::class,'doctor_id','id');   
   }
   public function insurance(){
      return $this->belongsTo(Insurances::class,'insurance_id','id');   
   }
}
