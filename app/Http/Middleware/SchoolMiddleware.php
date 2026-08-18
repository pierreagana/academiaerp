<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SchoolMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures superadmins cannot access school routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role === 'superadmin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Accès refusé. Les Super Administrateurs ne peuvent pas accéder aux tableaux de bord des écoles.']);
        }

        return $next($request);
    }
}
