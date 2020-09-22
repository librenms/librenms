<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Models\Notification;
use App\Models\NotificationAttrib;
use DB;

class MarkNotificationsRead
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserCreated $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $user = $event->user;
        // mark pre-existing notifications as read
        NotificationAttrib::query()->insert(Notification::whereNotExists(function ($query) use ($user) {
            return $query->select(DB::raw(1))
                ->from('notifications_attribs')
                ->whereRaw('notifications.notifications_id = notifications_attribs.notifications_id')
                ->where('notifications_attribs.user_id', $user->user_id);
        })->get()->map(function ($notif) use ($user) {
            return [
                'notifications_id' => $notif->notifications_id,
                'user_id' => $user->user_id,
                'key' => 'read',
                'value' => 1,
            ];
        })->toArray());

        \Log::info('Marked all notifications as read for user ' . $user->username);
    }
}
