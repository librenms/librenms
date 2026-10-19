<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationAttrib;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display active notifications.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Collection<int, Notification> $sticky */
        $sticky = collect($user->getNotifications('sticky'));
        $stickyIds = $sticky->pluck('notifications_id');
        /** @var Collection<int, Notification> $unread */
        $unread = collect($user->getNotifications('unread'))->whereNotIn('notifications_id', $stickyIds);

        $this->resolveUsernames($sticky->concat($unread));

        return view('notifications.index', [
            'isArchive' => false,
            'unreadCount' => $unread->count() + $sticky->count(),
            'sticky' => $sticky,
            'notifications' => $unread,
        ]);
    }

    /**
     * Display archived (read) notifications.
     */
    public function archive(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Collection<int, Notification> $read */
        $read = collect($user->getNotifications('read'));
        $this->resolveUsernames($read);

        return view('notifications.index', [
            'isArchive' => true,
            'unreadCount' => Notification::isUnread($user)->count(),
            'sticky' => collect(),
            'notifications' => $read,
        ]);
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Notification::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Notification::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'source' => (string) Auth::id(),
            'checksum' => hash('sha512', Auth::id() . '.LOCAL.' . $validated['title']),
            'datetime' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Created',
        ]);
    }

    /**
     * Mark the specified notification as read.
     */
    public function read(Notification $notification): JsonResponse
    {
        $notification->markRead();

        return response()->json([
            'status' => 'ok',
            'message' => 'Set as Read',
        ]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function readAll(): JsonResponse
    {
        $unread = Notification::isUnread(Auth::user())->pluck('notifications.notifications_id');
        $attribs = $unread->map(fn ($id) => [
            'notifications_id' => $id,
            'user_id' => Auth::id(),
            'key' => 'read',
            'value' => '1',
        ]);

        NotificationAttrib::insert($attribs->all());

        return response()->json([
            'status' => 'ok',
            'message' => 'All notifications set as read',
        ]);
    }

    /**
     * Mark the specified notification as sticky.
     */
    public function stick(Notification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification->markSticky();

        return response()->json([
            'status' => 'ok',
            'message' => 'Set as Sticky',
        ]);
    }

    /**
     * Remove sticky from the specified notification.
     */
    public function unstick(Notification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        NotificationAttrib::where('notifications_id', $notification->notifications_id)
            ->where('user_id', Auth::id())
            ->where('key', 'sticky')
            ->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Removed Sticky',
        ]);
    }

    /**
     * @param  Collection<int, Notification>  $notifications
     */
    private function resolveUsernames(Collection $notifications): void
    {
        $numericSources = $notifications->pluck('source')->filter(fn ($s) => is_numeric($s))->map(fn ($s) => (int) $s);
        $userIds = $notifications->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->merge($numericSources)->unique();
        $users = User::whereIn('user_id', $userIds)->pluck('username', 'user_id');

        foreach ($notifications as $notif) {
            if (is_numeric($notif->source)) {
                $notif->source = $users->get((int) $notif->source) ?? $notif->source;
            }
            if (! empty($notif->user_id)) {
                $notif->sticky_username = $users->get((int) $notif->user_id) ?? 'Unknown';
            }
        }
    }
}
