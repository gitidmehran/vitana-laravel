<?php

namespace App\Helpers;

use App\Models\User;
use Auth;

class Utility
{
    public static function appendRoles(&$data)
    {
        if (Auth::user()->role == "1") { //super Admin
            $data['created_user'] = Auth::id();
        } else {
            $data['clinic_id'] = @$data['clinic_id'] != "" ? $data['clinic_id'] : Auth::user()->clinic_id;
            $data['created_user'] = Auth::id();
        }
        return $data;
    }
}
