<?php

namespace App\Traits;

use IlluminateHttpRequest;

use App\Models\Scopes\ClinicScope;
use App\Models\ClinicUser;
use Auth;

/**
 * 
 */
trait HasClinicScope
{
    protected $clinicId = 1;
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ClinicScope());
    }
}
