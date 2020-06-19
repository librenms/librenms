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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LibreNMS\Interfaces\InstallerStep;

class MakeUserController extends InstallationController implements InstallerStep
{
    public function index(Request $request)
    {
        if (!self::enabled()) {
            return redirect()->route('install');
        }

        if (session('install.database')) {
            $user = User::adminOnly()->first();
        }

        if (isset($user)) {
            $this->markStepComplete('user');
            return view('install.user-created', $this->formatData([
                'user' => $user,
            ]));
        }

        return view('install.make-user', $this->formatData([
            'messages' => Arr::wrap(session('message'))
        ]));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            $user = new User($request->only(['username', 'password', 'email']));
            $user->level = 10;
            $user->setPassword($request->get('password'));
            $res = $user->save();
            $message = $res ? trans('install.user.success') : trans('install.user.failure');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return redirect()->back()->with('message', $message);
    }

    public function complete(): bool
    {
        return User::adminOnly()->exists();
    }

    public function enabled(): bool
    {
        return (bool)session('install.database');
    }

    public function icon(): string
    {
        return 'fa-key';
    }
}
