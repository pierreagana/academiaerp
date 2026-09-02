<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $parent = Auth::guard('parent')->user();
        $notifications = $parent->notificationLogs()->orderByDesc('created_at')->paginate(20);

        return view('ParentPortal::notifications', compact('notifications'));
    }

    public function markRead(NotificationLog $notification)
    {
        abort_unless($notification->parent_id === Auth::guard('parent')->id(), 404);

        $notification->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead()
    {
        Auth::guard('parent')->user()->notificationLogs()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
