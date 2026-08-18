<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Show the login form for school staff/admin.
     */
    public function showStaffLoginForm()
    {
        return view('SchoolDashboard::auth.login_staff');
    }

    /**
     * Show the login form for the portal (Student, Teacher, Parent).
     */
    public function showPortalLoginForm()
    {
        return view('SchoolDashboard::auth.login_portal');
    }
}
