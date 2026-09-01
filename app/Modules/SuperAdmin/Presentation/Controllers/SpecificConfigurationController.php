<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetSpecificConfigurationUseCase;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SpecificConfigurationController extends Controller
{
    public function __construct(
        private GetSpecificConfigurationUseCase $getSpecificConfigurationUseCase
    ) {}

    public function index()
    {
        $config = $this->getSpecificConfigurationUseCase->execute();

        return view('SuperAdmin::specific-configuration', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        // Checkbox values default to 0 if unchecked
        $checkboxes = [
            'payment_academia_pay',
            'payment_orange_money',
            'payment_wave',
            'payment_mtn',
            'payment_card',
            'ai_experimental_features',
            'ai_predictive_performance'
        ];

        foreach ($checkboxes as $cb) {
            if (!isset($data[$cb])) {
                $data[$cb] = '0';
            }
        }

        // Provider secret keys are never echoed back into the form — a blank
        // submission means "leave the existing value alone", not "clear it".
        // Non-blank submissions are encrypted before storage.
        $secretKeyFields = collect(['orange_money', 'wave', 'mtn', 'card'])
            ->map(fn ($provider) => $provider . '_secret_key');

        foreach ($secretKeyFields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '') {
                unset($data[$field]);
            } else {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        foreach ($data as $key => $value) {
            $formattedValue = $value;
            if (in_array($key, ['school_sectors', 'school_education_levels', 'school_language_regimes', 'school_academic_years']) && is_string($value)) {
                $items = array_values(array_filter(array_map('trim', explode(',', $value))));
                $formattedValue = json_encode($items, JSON_UNESCAPED_UNICODE);
            } elseif (is_array($value)) {
                $formattedValue = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            GlobalSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value'       => (string)$formattedValue,
                    'type'        => is_numeric($value) ? 'integer' : 'string',
                    'is_public'   => !$secretKeyFields->contains($key),
                    'description' => 'Configuration spécifique système',
                ]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('system_currency');

        return redirect()->back()->with('success', 'Configurations spécifiques enregistrées avec succès dans la base de données.');
    }
}
