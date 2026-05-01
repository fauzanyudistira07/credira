<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class UserNotificationApiController extends Controller
{
    public function index()
    {
        return response()->json(auth()->user()->notifications()->paginate(15));
    }

    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 404);
        $notification->update(['is_read' => true]);

        return $this->apiResponse([], 'Notifikasi ditandai dibaca.');
    }

    public function readAll()
    {
        auth()->user()->notifications()->update(['is_read' => true]);

        return $this->apiResponse([], 'Semua notifikasi ditandai dibaca.');
    }
}
