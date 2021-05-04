<?php
/**
 * UserController.php
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

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuthLog;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Support\Str;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use Toastr;
use URL;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('deny-demo');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage', User::class);

        return view('user.index', [
            'users' => User::orderBy('username')->get(),
            'multiauth' => User::query()->distinct('auth_type')->count('auth_type') > 1,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $tmp_user = new User;
        $tmp_user->can_modify_passwd = LegacyAuth::get()->canUpdatePasswords(); // default to true for new users

        return view('user.create', [
            'user' => $tmp_user,
            'dashboard' => null,
            'dashboards' => Dashboard::allAvailable($tmp_user)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        $user = $request->only(['username', 'realname', 'email', 'descr', 'level', 'can_modify_passwd']);
        $user['auth_type'] = LegacyAuth::getType();
        $user['can_modify_passwd'] = $request->get('can_modify_passwd'); // checkboxes are missing when unchecked

        $user = User::create($user);

        $user->setPassword($request->new_password);
        $user->auth_id = LegacyAuth::get()->getUserid($user->username) ?: $user->user_id;
        $this->updateDashboard($user, $request->get('dashboard'));

        if ($user->save()) {
            Toastr::success(__('User :username created', ['username' => $user->username]));

            return redirect(route('users.index'));
        }

        Toastr::error(__('Failed to create user'));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return $user->username;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $data = [
            'user' => $user,
            'dashboard' => UserPref::getPref($user, 'dashboard'),
            'dashboards' => Dashboard::allAvailable($user)->get(),
        ];

        if (Config::get('twofactor')) {
            $lockout_time = Config::get('twofactor_lock');
            $twofactor = UserPref::getPref($user, 'twofactor');
            $data['twofactor_enabled'] = isset($twofactor['key']);

            // if enabled and 3 or more failures
            $last_failure = isset($twofactor['last']) ? (time() - $twofactor['last']) : 0;
            $data['twofactor_locked'] = isset($twofactor['fails']) && $twofactor['fails'] >= 3 && (! $lockout_time || $last_failure < $lockout_time);
        }

        return view('user.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($request->get('new_password') && $user->canSetPassword($request->user())) {
            $user->setPassword($request->new_password);
        }

        $user->fill($request->all());

        if ($request->has('dashboard') && $this->updateDashboard($user, $request->get('dashboard'))) {
            Toastr::success(__('Updated dashboard for :username', ['username' => $user->username]));
        }

        if ($user->isDirty()) {
            if ($user->save()) {
                Toastr::success(__('User :username updated', ['username' => $user->username]));
            } else {
                Toastr::error(__('Failed to update user :username', ['username' => $user->username]));

                return redirect()->back();
            }
        }

        return redirect(route(Str::contains(URL::previous(), 'preferences') ? 'preferences.index' : 'users.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(__('User :username deleted.', ['username' => $user->username]));
    }

    /**
     * @param User $user
     * @param mixed $dashboard
     * @return bool
     */
    protected function updateDashboard(User $user, $dashboard)
    {
        if ($dashboard) {
            $existing = UserPref::getPref($user, 'dashboard');
            if ($dashboard != $existing) {
                UserPref::setPref($user, 'dashboard', $dashboard);

                return true;
            }
        }

        return false;
    }

    public function authlog()
    {
        $this->authorize('manage', User::class);

        return view('user.authlog', [
            'authlog' => AuthLog::orderBy('datetime', 'DESC')->get(),
        ]);
    }
}
