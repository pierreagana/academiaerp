<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'superadmin') {
            return redirect('/superadmin');
        }
        return view('SuperAdmin::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Verify superadmin role
            if (Auth::user()->role !== 'superadmin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Accès refusé. Ce compte n\'a pas les droits Super Administrateur.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended('/superadmin');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte Super Admin.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/superadmin/login');
    }
}
