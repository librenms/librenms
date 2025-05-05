<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VerificationController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = AppServiceProvider::HOME;

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('signed', only: ['verify']),
            new Middleware('throttle:6,1', only: ['verify', 'resend']),
        ];
    }
}
