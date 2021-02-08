<?php
/**
 * MakeUserController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LibreNMS\Interfaces\InstallerStep;

class MakeUserController extends InstallationController implements InstallerStep
{
    protected $step = 'user';

    public function index(Request $request)
    {
        if (! $this->initInstallStep()) {
            return $this->redirectToIncomplete();
        }

        if (session('install.database')) {
            $user = User::adminOnly()->first();
        }

        if (isset($user)) {
            $this->markStepComplete();

            return view('install.user-created', $this->formatData([
                'user' => $user,
            ]));
        }

        return view('install.make-user', $this->formatData([
            'messages' => Arr::wrap(session('message')),
        ]));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $message = trans('install.user.failure');

        try {
            // only allow the first admin to be created
            if (! $this->complete()) {
                $this->configureDatabase();
                $user = new User($request->only(['username', 'password', 'email']));
                $user->level = 10; // admin
                $user->setPassword($request->get('password'));
                $res = $user->save();

                if ($res) {
                    $message = trans('install.user.success');
                    $this->markStepComplete();
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return redirect()->back()->with('message', $message);
    }

    public function complete(): bool
    {
        if ($this->stepCompleted('user')) {
            return true;
        }

        try {
            if ($this->stepCompleted('database')) {
                $exists = User::adminOnly()->exists();
                if ($exists) {
                    $this->markStepComplete();
                }

                return $exists;
            }
        } catch (QueryException $e) {
            //
        }

        return false;
    }

    public function enabled(): bool
    {
        return $this->stepCompleted('database');
    }

    public function icon(): string
    {
        return 'fa-key';
    }
}
