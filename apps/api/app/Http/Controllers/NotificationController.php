<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(50)->get();

        // A person who's also a vendor has notifications filed under two
        // separate notifiable identities (their User record for customer
        // events, their Vendor record for vendor events) — merge both so
        // the bell shows a single unified feed.
        if ($user->vendor) {
            $notifications = $notifications
                ->merge($user->vendor->notifications()->latest()->take(50)->get())
                ->sortByDesc('created_at')
                ->take(50)
                ->values();
        }

        return ApiResponse::success($notifications->map(fn ($n) => $this->transform($n)), 'Notifications retrieved.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($user->vendor) {
            $user->vendor->unreadNotifications->markAsRead();
        }

        return ApiResponse::success(null, 'Notifications marked as read.');
    }

    protected function transform(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->data['type'] ?? null,
            'message' => $notification->data['message'] ?? '',
            'href' => $notification->data['href'] ?? null,
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at,
        ];
    }
}
