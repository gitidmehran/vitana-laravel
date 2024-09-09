<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacistPatient extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['patient_id','pharmacist_id','created_user','updated_user'];
}
