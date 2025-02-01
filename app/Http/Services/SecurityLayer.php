<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;

class SecurityLayer
{
    public function getRoleFromToken()
    {
        return Auth::user()->role->name;
    }

    public function getUsernameFromToken()
    {
        return Auth::user()->name;
    }

    public function getUserIdFromToken()
    {
        return Auth::user()->id;
    }
}
