<?php

namespace App\Http\Middleware;

use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolTrackAccess
{
    public function __construct(private SchoolTrackAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->access->isModuleEnabled()) {
            return response()->json([
                'message' => "Le module School Track n'est pas disponible actuellement.",
                'code' => 'module_disabled',
            ], 403);
        }

        $parent = $request->user();

        if (!$parent || !$parent->activeSchoolTrackSubscription()) {
            $status = $this->access->statusFor($parent);

            return response()->json([
                'message' => 'Un abonnement School Track actif est requis.',
                'code' => 'subscription_required',
                'plans' => $status['plans'],
                'paymentMethods' => $status['paymentMethods'],
            ], 403);
        }

        return $next($request);
    }
}
