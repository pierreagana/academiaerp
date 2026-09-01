<?php

namespace App\Modules\SchoolTrack\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use App\Modules\Finance\Domain\Models\Payment;
use Illuminate\Http\Request;

class SchoolTrackSubscriptionController extends Controller
{
    public function __construct(private SchoolTrackAccessService $access) {}

    public function status(Request $request)
    {
        return response()->json($this->access->statusFor($request->user()));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:' . implode(',', array_keys(SchoolTrackSubscription::PLAN_PRICES)),
            'payment_method' => 'nullable|in:' . implode(',', array_keys(Payment::METHODS)),
        ]);

        // Cash is the default payment method for parents (web & mobile) unless
        // they explicitly pick another one.
        $this->access->subscribe($request->user(), $validated['plan'], $validated['payment_method'] ?? 'cash');

        return response()->json($this->access->statusFor($request->user()));
    }
}
