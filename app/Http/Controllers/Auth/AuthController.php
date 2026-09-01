<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $facilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $availableSectors = School::getAvailableSectors();
        $availableLevels = School::getAvailableLevels();
        $availableLanguageRegimes = School::getAvailableLanguageRegimes();
        $saasPackages = \App\Modules\SuperAdmin\Domain\Models\SaasPackage::orderBy('price')->get();

        return view('SchoolDashboard::auth.register_staff', compact(
            'facilities', 'availableSectors', 'availableLevels', 'availableLanguageRegimes', 'saasPackages'
        ));
    }

    /**
     * Public "Demande de Démo" submission — does NOT provision the school or
     * log anyone in. It records a pending RegistrationRequest for a SuperAdmin
     * to review; the school and admin account only get created once
     * RegistrationRequestController::approve() runs. See that method for the
     * mirror image of the field mapping below.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'type' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'language_regime' => ['nullable', 'string', 'max:100'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['string', 'max:100'],
            'plan_name' => ['nullable', 'string'],
            'students_count' => ['nullable', 'integer', 'min:0'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'exists:countries,name'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
        ]);

        $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('logos/pending', 'public') : null;

        $region = collect([$validated['city'] ?? null, $validated['country'] ?? null])->filter()->implode(', ');

        \App\Modules\SuperAdmin\Domain\Models\RegistrationRequest::create([
            'school_name' => $request->school_name,
            'applicant_name' => $request->name,
            'email' => $request->email,
            'phone' => \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($validated['phone_country_code'] ?? null, $validated['phone_number']),
            'region' => $region ?: null,
            'status' => 'en attente',
            'plan_requested' => $validated['plan_name'] ?? 'Starter',
            'type' => $validated['type'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'language_regime' => $validated['language_regime'] ?? null,
            'levels' => $validated['levels'] ?? null,
            'students_count' => $validated['students_count'] ?? null,
            'slogan' => $request->slogan,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'logo_path' => $logoPath,
            'facilities' => $validated['facilities'] ?? null,
        ]);

        return redirect('/')->with('registration_submitted', true);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
