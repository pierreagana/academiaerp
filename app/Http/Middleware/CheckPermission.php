<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Directors/superadmins bypass; portal accounts (teacher/staff) need the
     * given permission slug + action (show/create/edit/update/delete) on
     * their assigned RBAC role's permission matrix. See User::canAccess().
     * Usage: permission:slug or permission:slug,action (defaults to 'show').
     */
    public function handle(Request $request, Closure $next, string $permission, string $action = 'show'): Response
    {
        if (!auth()->user()?->canAccess($permission, $action)) {
            abort(403, "Vous n'avez pas la permission d'accéder à cette page.");
        }

        return $next($request);
    }
}
