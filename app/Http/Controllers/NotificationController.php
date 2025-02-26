<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::where('read', false)->latest()->get();
    }

    public function markAsRead(Request $request)
    {
        Notification::whereIn('id', $request->input('ids'))->update(['read' => true]);
        return response()->json(['success' => true]);
    }
}
