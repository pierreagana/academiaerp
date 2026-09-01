<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationSettingsController extends Controller
{
    private const SERVICE_ACCOUNT_PATH = 'firebase/service-account.json';

    // Firebase's own client-config fields (apiKey, authDomain, ...) are safe to
    // expose — Firebase's security model relies on Security Rules, not on
    // hiding these. Only the service account JSON (server-side Admin SDK
    // credential) is a real secret, so it alone gets private disk storage and
    // is never echoed back into the form.
    private const FIELDS = [
        'firebase_api_key',
        'firebase_auth_domain',
        'firebase_storage_bucket',
        'firebase_messaging_sender_id',
        'firebase_app_id',
        'firebase_measurement_id',
        'firebase_project_id',
    ];

    public function index()
    {
        $raw = GlobalSetting::whereIn('key', self::FIELDS)->pluck('value', 'key');
        $settings = collect(self::FIELDS)->mapWithKeys(fn ($key) => [$key => $raw->get($key, '')]);
        $serviceAccountConfigured = Storage::disk('local')->exists(self::SERVICE_ACCOUNT_PATH);

        return view('SuperAdmin::notification-settings', compact('settings', 'serviceAccountConfigured'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'firebase_api_key' => ['nullable', 'string', 'max:255'],
            'firebase_auth_domain' => ['nullable', 'string', 'max:255'],
            'firebase_storage_bucket' => ['nullable', 'string', 'max:255'],
            'firebase_messaging_sender_id' => ['nullable', 'string', 'max:255'],
            'firebase_app_id' => ['nullable', 'string', 'max:255'],
            'firebase_measurement_id' => ['nullable', 'string', 'max:255'],
            'firebase_project_id' => ['required', 'string', 'max:255'],
            'firebase_service_account' => ['nullable', 'file', 'mimes:json', 'max:512'],
        ]);

        foreach (self::FIELDS as $field) {
            GlobalSetting::updateOrCreate(
                ['key' => $field],
                [
                    'value' => (string) ($validated[$field] ?? ''),
                    'type' => 'string',
                    'is_public' => true,
                    'description' => 'Configuration Firebase (notifications)',
                ]
            );
        }

        if ($request->hasFile('firebase_service_account')) {
            $contents = file_get_contents($request->file('firebase_service_account')->getRealPath());
            $decoded = json_decode($contents, true);

            if (json_last_error() !== JSON_ERROR_NONE || ($decoded['type'] ?? null) !== 'service_account') {
                return redirect()->route('superadmin.notification-settings')
                    ->with('error', "Le fichier fourni n'est pas un fichier de compte de service Firebase valide (JSON avec \"type\": \"service_account\" attendu).");
            }

            Storage::disk('local')->put(self::SERVICE_ACCOUNT_PATH, $contents);
        }

        return redirect()->route('superadmin.notification-settings')->with('success', 'Paramètres de notification Firebase enregistrés.');
    }

    public function downloadSample()
    {
        $sample = [
            'type' => 'service_account',
            'project_id' => 'votre-projet-firebase',
            'private_key_id' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nVOTRE_CLE_PRIVEE_ICI\n-----END PRIVATE KEY-----\n",
            'client_email' => 'firebase-adminsdk-xxxxx@votre-projet-firebase.iam.gserviceaccount.com',
            'client_id' => '000000000000000000000',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/exemple%40votre-projet-firebase.iam.gserviceaccount.com',
            'universe_domain' => 'googleapis.com',
        ];

        return response()->streamDownload(
            fn () => print(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            'firebase-service-account-exemple.json',
            ['Content-Type' => 'application/json']
        );
    }
}
