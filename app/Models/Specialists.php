<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClinicScope;

class Specialists extends Model
{
    use HasFactory,SoftDeletes,HasClinicScope;
    protected $fillable = ['name','short_name','clinic_id','created_user'];
}
