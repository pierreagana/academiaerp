<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function dashboard(ParentPortalService $service)
    {
        $parent = Auth::guard('parent')->user();
        $overview = $service->overview($parent);

        return view('ParentPortal::dashboard', array_merge(['parent' => $parent], $overview));
    }

    public function bulletin(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->bulletin($child);

        return view('ParentPortal::bulletin', array_merge(['child' => $child], $data));
    }

    public function attendance(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->attendance($child);

        return view('ParentPortal::attendance', array_merge(['child' => $child], $data));
    }

    public function homework(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->homework($child);

        return view('ParentPortal::homework', array_merge(['child' => $child], $data));
    }

    public function fees(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->fees($child);

        return view('ParentPortal::fees', array_merge(['child' => $child], $data));
    }

    public function canteen(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->canteen($child);

        return view('ParentPortal::canteen', array_merge(['child' => $child], $data));
    }

    public function transport(int $student, ParentPortalService $service)
    {
        $child = $service->ensureChildBelongsToParent(Auth::guard('parent')->user(), $student);
        $data = $service->transport($child);

        return view('ParentPortal::transport', array_merge(['child' => $child], $data));
    }
}
