<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;

use App\Models\User;
use App\Models\Clinic;


class ClinicUser extends Model
{
   use  HasFactory, SoftDeletes;

   protected $table = "clinic_users";
   protected $fillable = [
        'user_id',
        'clinic_id',
        'created_user'
   ];

   public function clinic()
   {
      return $this->belongsTo(Clinic::class,'clinic_id','id');
   }
   
   public function users()
   {
      return $this->belongsTo(User::class,'clinic_id','id');
   }

   /*public function user_data(){
      return $this->belongsTo(User::class,'user_id','id');   
   }*/
    
    
}
