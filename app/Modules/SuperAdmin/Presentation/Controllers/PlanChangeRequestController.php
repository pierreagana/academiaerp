<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\PlanChangeRequest;

class PlanChangeRequestController extends Controller
{
    public function index()
    {
        $requests = PlanChangeRequest::with(['school', 'requestedPackage', 'requestedBy'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        $pendingCount = $requests->where('status', 'pending')->count();

        return view('SuperAdmin::plan-change-requests', compact('requests', 'pendingCount'));
    }

    public function approve(int $id)
    {
        $request = PlanChangeRequest::with(['school', 'requestedPackage'])->findOrFail($id);

        $request->school->update(['plan_name' => $request->requestedPackage->name]);

        $request->update([
            'status' => 'approved',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()->route('superadmin.plan-change-requests')
            ->with('success', "« {$request->school->name} » est passée au forfait « {$request->requestedPackage->name} ».");
    }

    public function reject(int $id)
    {
        $request = PlanChangeRequest::findOrFail($id);
        $request->update([
            'status' => 'rejected',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()->route('superadmin.plan-change-requests')
            ->with('success', 'Demande de changement de forfait refusée.');
    }
}
