<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetSpecificConfigurationUseCase;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Http\Request;

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

        foreach ($data as $key => $value) {
            GlobalSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value'       => is_array($value) ? json_encode($value) : (string)$value,
                    'type'        => is_numeric($value) ? 'integer' : 'string',
                    'is_public'   => true,
                    'description' => 'Configuration spécifique système',
                ]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('system_currency');

        return redirect()->back()->with('success', 'Configurations spécifiques enregistrées avec succès dans la base de données.');
    }
}
