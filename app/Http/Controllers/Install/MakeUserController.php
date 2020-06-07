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
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;

class MakeUserController extends \App\Http\Controllers\Controller
{
    use UsesDatabase;

    public function __invoke(Request $request)
    {
        if ($request->method() == 'POST') {
            $this->create($request);
        }

        if (session('install.database')) {
            $this->setDB();
            $user = User::first();
        }

        if (isset($user)) {
            session(['install.user' => true]);
            return view('install.user-created', [
                'user' => $user,
            ]);
        }

        return view('install.make-user');
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);


        try {
            $user = new User($request->only(['username', 'password', 'email']));
            $user->setPassword($request->get('password'));
            $user->setConnection($this->connection);
            $res = $user->save();
            $message = $res ? trans('install.user.success') : trans('install.user.failure');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

//        return redirect()->back()->with('message', $message);
    }
}
