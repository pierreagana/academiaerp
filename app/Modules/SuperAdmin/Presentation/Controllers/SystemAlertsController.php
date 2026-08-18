<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\SuperAdmin\Application\UseCases\ListSystemAlertsUseCase;
use App\Modules\SuperAdmin\Domain\Models\SystemAlertRule;

class SystemAlertsController extends Controller
{
    public function __construct(
        private ListSystemAlertsUseCase $listSystemAlertsUseCase
    ) {}

    public function index()
    {
        $data = $this->listSystemAlertsUseCase->execute();

        return view('SuperAdmin::system-alerts', [
            'alerts'            => $data['alerts'],
            'kpis'              => $data['kpis'],
            'configurations'    => $data['configurations'],
            'severityBreakdown' => $data['severityBreakdown'],
        ]);
    }

    public function toggle(int $id)
    {
        $rule = SystemAlertRule::find($id);
        if ($rule) {
            $rule->update(['is_active' => !$rule->is_active]);
        }
        return redirect()->route('superadmin.system-alerts')->with('success', 'Statut de la règle d\'alerte mis à jour avec succès.');
    }

    public function destroy(int $id)
    {
        $rule = SystemAlertRule::find($id);
        if ($rule) {
            $rule->delete();
        }
        return redirect()->route('superadmin.system-alerts')->with('success', 'Règle d\'alerte supprimée avec succès.');
    }
}
