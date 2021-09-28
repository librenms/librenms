<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        $user->apiTokens()->delete();
        $user->notificationAttribs()->delete();
        $user->preferences()->delete();
        $user->pushSubscriptions()->delete();
    }
}
