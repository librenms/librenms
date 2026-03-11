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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuthLog;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\UserPref;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LibreNMS\Authentication\LegacyAuth;
use Spatie\Permission\Models\Role;
use URL;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('deny-demo');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('user.index', [
            'users' => User::with(['preferences', 'roles'])->orderBy('username')->get(),
            'multiauth' => User::query()->distinct()->count('auth_type') > 1,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $tmp_user = new User;
        $tmp_user->can_modify_passwd = LegacyAuth::getType() == 'mysql' ? 1 : 0; // default to true mysql

        return view('user.create', [
            'user' => $tmp_user,
            'dashboard' => null,
            'dashboards' => Dashboard::allAvailable($tmp_user)->get(),
            'timezone' => 'default',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $request->only(['username', 'realname', 'email', 'descr', 'can_modify_passwd']);
        $user['auth_type'] = LegacyAuth::getType();
        $user['can_modify_passwd'] = $request->input('can_modify_passwd'); // checkboxes are missing when unchecked

        $user = User::create($user);

        $user->setPassword($request->new_password);
        $user->syncRoles($request->input('roles', []));
        $user->auth_id = (string) LegacyAuth::get()->getUserid($user->username) ?: $user->user_id;
        $this->updateDashboard($user, $request->integer('dashboard'));
        $this->updateTimezone($user, $request->string('timezone'));

        if ($user->save()) {
            $toast->success(__('User :username created', ['username' => $user->username]));

            return redirect(route('users.index'));
        }

        $toast->error(__('Failed to create user'));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): string
    {
        $this->authorize('view', $user);

        return $user->username;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $data = [
            'user' => $user,
            'dashboard' => UserPref::getPref($user, 'dashboard'),
            'dashboards' => Dashboard::allAvailable($user)->get(),
            'timezone' => UserPref::getPref($user, 'timezone') ?: 'default',
        ];

        if (LibrenmsConfig::get('twofactor')) {
            $lockout_time = LibrenmsConfig::get('twofactor_lock');
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
     */
    public function update(UpdateUserRequest $request, User $user, ToastInterface $toast): RedirectResponse
    {
        if ($request->input('new_password')) {
            $user->setPassword($request->new_password);
            /** @var User $current_user */
            $current_user = Auth::user();
            Auth::setUser($user); // make sure new password is loaded, can only logout other sessions for the active user
            Auth::logoutOtherDevices($request->new_password);

            // when setting the password on another account, restore back to the user's account.
            if ($current_user->user_id !== $user->user_id) {
                Auth::setUser($current_user);
            }
        }

        $user->fill($request->validated());

        if ($request->has('roles') && $request->user()->can('update', Role::class)) {
            $user->syncRoles($request->input('roles', []));
        }

        if ($request->has('dashboard') && $this->updateDashboard($user, $request->input('dashboard'))) {
            $toast->success(__('Updated dashboard for :username', ['username' => $user->username]));
        }

        if ($request->has('timezone') && $this->updateTimezone($user, $request->string('timezone'))) {
            if ($request->input('timezone') != 'default') {
                $toast->success(__('Updated timezone for :username', ['username' => $user->username]));
            } else {
                $toast->success(__('Cleared timezone for :username', ['username' => $user->username]));
            }
        }

        if ($user->save()) {
            $toast->success(__('User :username updated', ['username' => $user->username]));

            return redirect(route(Str::contains(URL::previous(), 'preferences') ? 'preferences.index' : 'users.index'));
        }

        $toast->error(__('Failed to update user :username', ['username' => $user->username]));

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(__('User :username deleted.', ['username' => $user->username]));
    }

    protected function updateDashboard(User $user, int $dashboard): bool
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

    protected function updateTimezone(User $user, string $timezone): bool
    {
        $existing = UserPref::getPref($user, 'timezone');
        if ($timezone != 'default') {
            if (! in_array($timezone, timezone_identifiers_list())) {
                return false;
            }

            if ($timezone != $existing) {
                UserPref::setPref($user, 'timezone', $timezone);

                return true;
            }
        } else {
            if ($existing != '') {
                UserPref::forgetPref($user, 'timezone');

                return true;
            }
        }

        return false;
    }

    public function authlog(): View
    {
        $this->authorize('view', AuthLog::class);

        return view('user.authlog', [
            'authlog' => AuthLog::orderBy('datetime', 'DESC')->get(),
        ]);
    }
}
