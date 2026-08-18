<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures only users with the 'superadmin' role can access SuperAdmin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('superadmin.login');
        }

        if (Auth::user()->role !== 'superadmin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('superadmin.login')
                ->withErrors(['email' => 'Accès refusé. Vous n\'avez pas les droits Super Administrateur.']);
        }

        return $next($request);
    }
}
