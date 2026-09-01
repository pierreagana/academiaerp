<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolTrackWebController extends Controller
{
    public function subscribe(Request $request, SchoolTrackAccessService $access)
    {
        $validated = $request->validate([
            'plan' => 'required|in:' . implode(',', array_keys(SchoolTrackSubscription::PLAN_PRICES)),
            'payment_method' => 'nullable|in:' . implode(',', array_keys(Payment::METHODS)),
        ]);

        // Cash is the default payment method for parents (web & mobile) unless
        // they explicitly pick another one.
        $access->subscribe(Auth::guard('parent')->user(), $validated['plan'], $validated['payment_method'] ?? 'cash');

        return redirect()->route('parent.dashboard')->with('success', 'Votre abonnement School Track est actif !');
    }
}
