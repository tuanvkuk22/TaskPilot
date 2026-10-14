<?php

namespace ProjectsManager\Modules\Notifications\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'all')->toString();
        $query = Auth::user()->notifications()->orderBy('created_at', 'desc');
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        } else {
            $filter = 'all';
        }
        $notifications = $query->paginate(20)->withQueryString();

        return view('notifications.index', ['notifications' => $notifications, 'filter' => $filter]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function recentNotifications(Request $request): JsonResponse
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'url' => $n->data['url'] ?? null,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
    }

    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroy(string $id): JsonResponse|RedirectResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Đã xóa thông báo.');
    }
}
