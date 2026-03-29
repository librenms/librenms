<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the user "deleted" event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleted(User $user): void
    {
        // Remove permission rows that reference this user (alternative to FK cascade)
        DB::table('ports_perms')->where('user_id', $user->user_id)->delete();
        DB::table('devices_perms')->where('user_id', $user->user_id)->delete();
        DB::table('bill_perms')->where('user_id', $user->user_id)->delete();

        // Remove related user-owned records
        $user->apiTokens()->delete();
        $user->notificationAttribs()->delete();
        $user->preferences()->delete();
        $user->pushSubscriptions()->delete();
    }
}
