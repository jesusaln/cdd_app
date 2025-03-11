<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('read', false)->latest()->get();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:notifications,id',
        ]);

        Notification::whereIn('id', $request->ids)->update(['read' => true]);

        return response()->json(['message' => 'Notificaciones marcadas como leídas']);
    }
}
