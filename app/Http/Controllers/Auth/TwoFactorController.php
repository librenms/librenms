<?php
/**
 * TwoFactorController.php
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
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use Session;
use Toastr;

class TwoFactorController extends Controller
{
    public function verifyTwoFactor(Request $request)
    {
        $this->validate($request, [
            'twofactor' => 'required|numeric',
        ]);

        try {
            $this->checkToken($request->user(), $request->input('twofactor'));
        } catch (AuthenticationException $e) {
            return redirect()->route('2fa.form')->withErrors($e->getMessage());
        }

        // token validated
        if (session('twofactorremove')) {
            UserPref::forgetPref(auth()->user(), 'twofactor');
            $request->session()->forget(['twofactor', 'twofactorremove']);

            \Toastr::info(__('TwoFactor auth removed.'));

            return redirect('preferences');
        }

        $request->session()->put('twofactor', true);

        return redirect()->intended();
    }

    public function showTwoFactorForm(Request $request)
    {
        $twoFactorSettings = $this->loadSettings($request->user());

        // don't allow visiting this page if not needed
        if (empty($twoFactorSettings) || ! Config::get('twofactor') || session('twofactor')) {
            return redirect()->intended();
        }

        $errors = [];

        // lockout the user if there are too many failures
        if (isset($twoFactorSettings['fails']) && $twoFactorSettings['fails'] >= 3) {
            $lockout_time = Config::get('twofactor_lock', 0);

            if (! $lockout_time) {
                $errors['lockout'] = __('Too many two-factor failures, please contact administrator.');
            } elseif ((time() - $twoFactorSettings['last']) < $lockout_time) {
                $errors['lockout'] = __('Too many two-factor failures, please wait :time seconds', ['time' => $lockout_time]);
            }
        }

        return view('auth.2fa')->with([
            'key' => $twoFactorSettings['key'],
            'uri' => TwoFactor::generateUri($request->user()->username, $twoFactorSettings['key'], $twoFactorSettings['counter'] !== false),
        ])->withErrors($errors);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse.
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'twofactor' => Rule::in('time', 'counter'),
        ]);

        $key = TwoFactor::genKey();

        // assume time based
        $settings = [
            'key' => $key,
            'fails' => 0,
            'last' => 0,
            'counter' => $request->get('twofactortype') == 'counter' ? 0 : false,
        ];

        Session::put('twofactoradd', $settings);

        return redirect()->intended();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse.
     */
    public function destroy(Request $request)
    {
        $request->session()->put('twofactorremove', true);
        $request->session()->forget('twofactor');

        return redirect()->intended();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse.
     */
    public function cancelAdd(Request $request)
    {
        $request->session()->forget('twofactoradd');

        return redirect()->intended();
    }

    /**
     * @param User $user
     * @param int $token
     * @return true
     * @throws AuthenticationException
     */
    private function checkToken($user, $token)
    {
        if (! $token) {
            throw new AuthenticationException(__('No Two-Factor Token entered.'));
        }

        // check if this is new
        $twoFactorSettings = $this->loadSettings($user);

        if (empty($twoFactorSettings)) {
            throw new AuthenticationException(__('No Two-Factor settings, how did you get here?'));
        }

        if (($server_count = TwoFactor::verifyHOTP($twoFactorSettings['key'], $token, $twoFactorSettings['counter'])) === false) {
            if (isset($twoFactorSettings['fails'])) {
                $twoFactorSettings['fails']++;
            } else {
                $twoFactorSettings['fails'] = 1;
            }
            $twoFactorSettings['last'] = time();
            UserPref::setPref($user, 'twofactor', $twoFactorSettings);
            throw new AuthenticationException(__('Wrong Two-Factor Token.'));
        }

        // update counter
        if ($twoFactorSettings['counter'] !== false) {
            if ($server_count !== true && $server_count !== $twoFactorSettings['counter']) {
                $twoFactorSettings['counter'] = $server_count + 1;
            } else {
                $twoFactorSettings['counter']++;
            }
        }

        // success
        $twoFactorSettings['fails'] = 0;
        UserPref::setPref($user, 'twofactor', $twoFactorSettings);

        // notify if added
        if (Session::has('twofactoradd')) {
            Toastr::success(__('TwoFactor auth added.'));
            Session::forget('twofactoradd');
        }

        return true;
    }

    /**
     * @return mixed
     */
    private function loadSettings(User $user)
    {
        if (Session::has('twofactoradd')) {
            return Session::get('twofactoradd');
        }

        return UserPref::getPref($user, 'twofactor');
    }
}
