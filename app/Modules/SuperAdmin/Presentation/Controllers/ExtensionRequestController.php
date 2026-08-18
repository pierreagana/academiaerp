<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\SchoolExtensionRequest;

class ExtensionRequestController extends Controller
{
    public function index()
    {
        $requests = SchoolExtensionRequest::with(['school', 'requestedBy'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        $pendingCount = $requests->where('status', 'pending')->count();

        return view('SuperAdmin::extension-requests', compact('requests', 'pendingCount'));
    }

    public function approve(int $id)
    {
        $request = SchoolExtensionRequest::findOrFail($id);
        $request->update([
            'status'     => 'approved',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()->route('superadmin.extension-requests')
            ->with('success', "Extension « {$request->module_name} » activée pour {$request->school->name}.");
    }

    public function reject(int $id)
    {
        $request = SchoolExtensionRequest::findOrFail($id);
        $request->update([
            'status'     => 'rejected',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()->route('superadmin.extension-requests')
            ->with('success', "Demande d'extension « {$request->module_name} » refusée.");
    }
}
