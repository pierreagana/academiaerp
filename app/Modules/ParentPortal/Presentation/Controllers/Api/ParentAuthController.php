<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use App\Modules\Academic\Domain\Models\ParentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    public function register(Request $request, ParentPortalAccountService $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:parent_accounts,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['required', 'string'],
        ], [
            'phone.unique' => 'Un compte existe déjà avec ce numéro. Connectez-vous plutôt.',
        ]);

        $account = $service->registerSelf($data);
        $token = $account->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'parent' => ['id' => $account->id, 'name' => $account->name, 'phone' => $account->phone],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string'],
        ]);

        $account = ParentAccount::where('phone', $data['phone'])->first();

        if (!$account || !Hash::check($data['password'], $account->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $token = $account->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'parent' => ['id' => $account->id, 'name' => $account->name, 'phone' => $account->phone],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }
}
