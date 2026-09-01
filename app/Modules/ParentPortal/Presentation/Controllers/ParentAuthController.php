<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\SuperAdmin\Domain\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('ParentPortal::auth.login');
    }

    public function showRegisterForm()
    {
        return view('ParentPortal::auth.register');
    }

    public function register(Request $request, ParentPortalAccountService $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['phone'] = Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number']);
        unset($data['phone_country_code'], $data['phone_number']);

        // Not a plain unique:parent_accounts,phone rule — an existing account may
        // still be in the old bare-digits format while this form always submits
        // the new "+225 ..." one, so an exact-string check would miss real dupes.
        if (Country::applyPhoneMatch(ParentAccount::query(), 'phone', $data['phone'])->exists()) {
            return back()->withErrors(['phone_number' => 'Un compte existe déjà avec ce numéro. Connectez-vous plutôt.'])->withInput();
        }

        $account = $service->registerSelf($data);

        Auth::guard('parent')->login($account);
        $request->session()->regenerate();

        return redirect()->route('parent.children.add-form');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Matches across both storage eras (bare local digits vs "+225 ..."
        // combined) instead of Auth::attempt()'s exact-string comparison —
        // see Country::applyPhoneMatch() for why that distinction matters here.
        $account = Country::applyPhoneMatch(ParentAccount::query(), 'phone', $credentials['phone'])->first();

        if (!$account || !Hash::check($credentials['password'], $account->password)) {
            return back()->withErrors([
                'phone' => 'Identifiants incorrects.',
            ])->onlyInput('phone');
        }

        Auth::guard('parent')->login($account, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('parent.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('parent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('parent.login');
    }
}
