<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medications extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medications';
    protected $fillable = ['patient_id','name','dose', 'description','condition', 'status', 'created_user','status','detele_at'];
}