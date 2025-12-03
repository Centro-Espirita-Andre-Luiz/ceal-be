<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect('/ceal');
        }

        return redirect('/ceal/login');
    }
}
