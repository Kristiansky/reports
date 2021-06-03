<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use Auth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\Authentication;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    
    public function login()
    {
        return view('auth.login');
    }
    
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        
        $credentials = $request->only('email', 'password');
        
        if (Authentication::attempt($credentials)) {
            return redirect()->intended('/');
        }
        
        return redirect('login')->with('error', 'Oppes! You have entered invalid credentials');
    }
    
    public function logout() {
        Auth::logout();
        Session::flush();
        
        return redirect('login');
    }
    
}
