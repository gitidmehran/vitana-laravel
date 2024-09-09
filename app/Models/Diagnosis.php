<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'diagnosis';
    protected $fillable = ['patient_id','condition','description','created_user','status','detele_at'];
}