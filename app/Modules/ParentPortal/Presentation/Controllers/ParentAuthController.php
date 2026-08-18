<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'phone' => ['required', 'string', 'max:30', 'unique:parent_accounts,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.unique' => 'Un compte existe déjà avec ce numéro. Connectez-vous plutôt.',
        ]);

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

        if (!Auth::guard('parent')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'phone' => 'Identifiants incorrects.',
            ])->onlyInput('phone');
        }

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
