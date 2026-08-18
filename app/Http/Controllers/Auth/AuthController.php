<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Modules\SuperAdmin\Domain\Models\School;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('SchoolDashboard::auth.login_staff');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
            'school_code' => ['nullable', 'string'],
        ]);

        $identifier = $request->input('email');
        $schoolCode = $request->input('school_code');

        // Admin tab: the account that created the school (main branch owner)
        // has a globally unique email, so it can be located without a school
        // code.
        if (empty($schoolCode)) {
            $user = User::where('email', $identifier)->where('role', 'adminschool')->first();

            if ($user && Hash::check($request->input('password'), $user->password)) {
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended(route('school.dashboard'));
            }

            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
        }

        // Directeur / Personnel tab: portal accounts are scoped to their
        // establishment via the school code, then matched by email or
        // login_id (matricule).
        $school = School::where('code', $schoolCode)->first();

        if (!$school) {
            return back()->withErrors([
                'school_code' => "Aucun établissement ne correspond à ce code école.",
            ])->onlyInput('email', 'school_code');
        }

        $user = User::where('school_id', $school->id)
            ->where(fn ($q) => $q->where('email', $identifier)->orWhere('login_id', $identifier))
            ->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            if ($user->role === 'superadmin') {
                return back()->withErrors([
                    'email' => 'Les Super Administrateurs doivent utiliser le portail dédié',
                ])->onlyInput('email', 'school_code');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('school.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email', 'school_code');
    }

    public function showRegistrationForm()
    {
        return view('SchoolDashboard::auth.register_staff');
    }

    public function register(Request $request)
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_name' => ['nullable', 'string'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $logoUrl = Storage::url($path);
        }
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', \Illuminate\Support\Str::ascii($request->school_name));
        $code = 'ACAD-' . strtoupper(substr($cleanName, 0, 3)) . rand(1000, 9999);

        $school = School::create([
            'name' => $request->school_name,
            'code' => $code,
            'plan_name' => $request->plan_name,
            'slogan' => $request->slogan,
            'location' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'contact_email' => $request->email,
            'contact_phone' => $request->phone,
            'logo_url' => $logoUrl,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'adminschool',
            'school_id' => $school->id,
        ]);

        Auth::login($user);

        return redirect(route('school.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
