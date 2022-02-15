<?php
/**
 * SocialiateController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link https://www.librenms.org
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use LibreNMS\Config as LibreNMSConfig;
use LibreNMS\Exceptions\AuthenticationException;
use Log;

class SocialiteController extends Controller
{
    /** @var SocialiteUser */
    private $socialite_user;

    public function __construct()
    {
        $this->injectConfig();
    }

    public static function registerEventListeners(): void
    {
        foreach (LibreNMSConfig::get('auth.socialite.configs', []) as $provider => $config) {
            // Treat not set as "disabled"
            if (! isset($config['listener'])) {
                continue;
            }
            $listener = $config['listener'];

            if (class_exists($listener)) {
                Event::listen(\SocialiteProviders\Manager\SocialiteWasCalled::class, "$listener@handle");
            } else {
                Log::error("Wrong value for auth.socialite.configs.$provider.listener set, class: '$listener' does not exist!");
            }
        }
    }

    /**
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function redirect(Request $request, string $provider)
    {
        // Re-store target url since it will be forgotten after the redirect
        $request->session()->put('url.intended', redirect()->intended()->getTargetUrl());

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->socialite_user = Socialite::driver($provider)->user();

        // If we already have a valid session, user is trying to pair their account
        if (Auth::user()) {
            return $this->pairUser($provider);
        }

        $this->register($provider);

        return $this->login($provider);
    }

    /**
     * Metadata endpoint used in SAML
     */
    public function metadata(Request $request, string $provider): \Illuminate\Http\Response
    {
        $socialite = Socialite::driver($provider);

        if (method_exists($socialite, 'getServiceProviderMetadata')) {
            return $socialite->getServiceProviderMetadata();
        }

        return abort(404);
    }

    private function login(string $provider): RedirectResponse
    {
        $user = User::where('auth_type', "socialite_$provider")
            ->where('auth_id', $this->socialite_user->getId())
            ->first();

        try {
            if (! $user) {
                throw new AuthenticationException();
            }

            Auth::login($user);

            return redirect()->intended();
        } catch (AuthenticationException $e) {
            flash()->addError($e->getMessage());
        }

        return redirect()->route('login');
    }

    private function register(string $provider): void
    {
        if (! LibreNMSConfig::get('auth.socialite.register', false)) {
            return;
        }

        $user = User::firstOrNew([
            'auth_type' => "socialite_$provider",
            'auth_id'   => $this->socialite_user->getId(),
        ]);

        if ($user->user_id) {
            return;
        }

        $user->username = $this->buildUsername();
        $user->email = $this->socialite_user->getEmail();
        $user->realname = $this->buildRealName();

        $user->save();
    }

    private function pairUser(string $provider): RedirectResponse
    {
        $user = Auth::user();
        $user->auth_type = "socialite_$provider";
        $user->auth_id   = $this->socialite_user->getId();

        $user->save();

        return redirect()->route('preferences.index');
    }

    private function buildUsername(): string
    {
        return $this->socialite_user->getNickname()
        ?: $this->socialite_user->getEmail()
        ?: $this->buildRealName();
    }

    private function buildRealName(): string
    {
        $name = '';

        // These methods only exist for a few providers
        if (method_exists($this->socialite_user, 'getFirstName')) {
            $name = $this->socialite_user->getFirstName();
        }

        if (method_exists($this->socialite_user, 'getLastName')) {
            $name = trim($name . ' ' . $this->socialite_user->getLastName());
        }

        if (empty($name)) {
            $name = $this->socialite_user->getName();
        }

        return ! empty($name) ? $name : '';
    }

    /**
     * Take the config from Librenms Config, and insert it into Laravel Config
     */
    private function injectConfig(): void
    {
        foreach (LibreNMSConfig::get('auth.socialite.configs', []) as $provider => $config) {
            Config::set("services.$provider", $config);

            // Inject redirect URL automatically if not set
            if (! Config::has("services.$provider.redirect")) {
                Config::set("services.$provider.redirect",
                    route('socialite.callback', [$provider])
                );
            }

            // Inject SAML redirect url automatically
            $this->injectSAML2Config($provider);
        }
    }

    private function injectSAML2Config(string $provider): void
    {
        if ($provider !== 'saml2') {
            return;
        }

        if (! Config::has("services.$provider.sp_acs")) {
            Config::set("services.$provider.sp_acs", route('socialite.callback', [$provider]));
        }

        if (! Config::has("services.$provider.client_id")) {
            Config::set("services.$provider.client_id", '');
        }

        if (! Config::has("services.$provider.client_secret")) {
            Config::set("services.$provider.client_secret", '');
        }
    }
}
