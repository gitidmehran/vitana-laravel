<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurgicalHistory extends Model
{

    use HasFactory,SoftDeletes;
    protected $table = 'surgical_history';
    protected $fillable = ['patient_id','created_user','procedure','reason','date','surgeon','status'];
}



