<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use LibreNMS\Config;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username(): string
    {
        return 'username';
    }

    /**
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showLoginForm(Request $request)
    {
        // Check if we want to redirect users to the socialite provider directly
        if (! $request->has('redirect') && ! $request->session()->has('block_auto_redirect') && Config::get('auth.socialite.redirect') && array_key_first(Config::get('auth.socialite.configs', []))) {
            return (new SocialiteController)->redirect($request, array_key_first(Config::get('auth.socialite.configs', [])));
        }

        if (Config::get('public_status')) {
            $devices = Device::isActive()->with('location')->get();

            return view('auth.public-status')->with('devices', $devices);
        }

        return view('auth.login');
    }

    protected function loggedOut(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect(Config::get('auth_logout_handler', $this->redirectTo));
    }
}
