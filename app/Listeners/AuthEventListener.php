<?php

namespace App\Listeners;

use App\Checks;
use App\Events\Event;
use App\Models\User;
use DB;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use LibreNMS\Authentication\LegacyAuth;
use Request;
use Session;
use Toastr;

class AuthEventListener
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
     * Handle the login event.
     *
     * @param Login $event
     * @return void
     */
    public function login(Login $event)
    {
        /** @var User $user */
        $user = $event->user ?: (object)['username' => 'Not found'];

        DB::table('authlog')->insert(['user' => $user->username ?: '', 'address' => Request::ip(), 'result' => 'Logged In']);

        Toastr::info('Welcome ' . ($user->realname ?: $user->username));

        // Authenticated, set up legacy session stuff.  TODO Remove once ajax and graphs are ported to Laravel.
        LegacyAuth::setUpLegacySession();
    }

    /**
     * Handle the logout event.
     *
     * @param Logout $event
     * @return void
     */
    public function logout(Logout $event)
    {
        /** @var User $user */
        $user = $event->user ?: (object)['username' => 'Not found'];

        DB::table('authlog')->insert(['user' => $user->username ?: '', 'address' => Request::ip(), 'result' => 'Logged Out']);

        @session_start();
        unset($_SESSION['authenticated']);
        session_destroy();
    }
}
