<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    public function adminNotifications()
    {
        $user = Auth::user();

        // Get unread notifications
        $notifications = $user->unreadNotifications;

        return response()->json($notifications);
    }
}

