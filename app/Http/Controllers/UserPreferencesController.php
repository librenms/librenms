<?php
/**
 * UserPreferencesController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Device;
use App\Models\UserPref;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Config;
use Session;

class UserPreferencesController extends Controller
{
    public function __construct()
    {
        $this->middleware('deny-demo');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $data = [
            'user' => $user,
            'can_change_password' => LegacyAuth::get()->canUpdatePasswords($user->username),
            'dashboards' => Dashboard::allAvailable($user)->with('user')->get(),
            'default_dashboard' => UserPref::getPref($user, 'dashboard'),
            'note_to_device' => UserPref::getPref($user, 'add_schedule_note_to_device'),
            'locale' => UserPref::getPref($user, 'locale') ?: 'en',
            'locales' => $this->getValidLocales(),
        ];

        if (Config::get('twofactor')) {
            $twofactor = UserPref::getPref($user, 'twofactor');
            if ($twofactor) {
                $data['twofactor_uri'] = TwoFactor::generateUri($user->username, $twofactor['key'], $twofactor['counter'] !== false);
            }
            $data['twofactor'] = $twofactor;
        }

        if (!$user->hasGlobalRead()) {
            $data['devices'] = Device::hasAccess($user)->get();
        }

        return view('user.preferences', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $valid_prefs = [
            'dashboard' => 'required|integer',
            'add_schedule_note_to_device' => 'required|integer',
            'locale' => [
                'required',
                Rule::in(array_keys($this->getValidLocales())),
            ],
        ];

        $this->validate($request, [
            'pref' => ['required', Rule::in(array_keys($valid_prefs))],
            'value' => $valid_prefs[$request->pref] ?? 'required|integer',
        ]);

        UserPref::setPref($request->user(), $request->pref, $request->value);

        if ($request->pref == 'locale') {
            Session::put('locale', $request->value);
        }

        return response()->json(['status' => 'success']);
    }

    private function getValidLocales()
    {
        return array_reduce(glob(resource_path('lang') . '/*', GLOB_ONLYDIR), function ($locales, $locale) {
            {
                $locale = basename($locale);
                $lang = __('preferences.lang', [], $locale);
                $locales[$locale] = ($lang == 'preferences.lang' ? $locale : $lang);
                return $locales;
            }
        }, []);
    }
}
