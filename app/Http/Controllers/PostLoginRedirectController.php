<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostLoginRedirectController extends Controller
{
    public function redirect()
    {
        if (Auth::check()) {
            if (Auth::user()->hasRole('admin')) {
                return redirect()->intended('/admin');
            }

            if (Auth::user()->hasRole('psicologo')) {
                return redirect()->intended('/psicologo');
            }

            if (Auth::user()->hasRole('padre')) {
                return redirect()->intended('/padre');
            }
        }

        return redirect()->intended('/');
    }
}