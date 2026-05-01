<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('user.notifications.index', [
            'notifications' => auth()->user()->notifications()->paginate(15),
        ]);
    }

    public function read(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 404);
        $notification->update(['is_read' => true]);

        return back();
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->notifications()->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }
}
