<?php
/**
 * DatabaseController.php
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

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LibreNMS\DB\Eloquent;

class DatabaseController extends \App\Http\Controllers\Controller
{
    const KEYS = ['host', 'username', 'password', 'database', 'port', 'unix_socket'];

    public function __invoke(Request $request)
    {
        $data = Arr::only(session()->get('db') ?: [], self::KEYS);
        $data['stage'] = 2;

        return view('install.database', $data);
    }

    public function test(Request $request)
    {
        Eloquent::setConnection(
            'setup',
            $request->get('host', 'localhost'),
            $request->get('username', 'librenms'),
            $request->get('password', ''),
            $request->get('database', 'librenms'),
            $request->get('port', 3306),
            $request->get('unix_socket')
        );

        session()->put('db', Arr::only(config('database.connections.setup', []), self::KEYS));

        $ok = false;
        $message = '';
        try {
            $ok = Eloquent::isConnected('setup');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        session(['install.database' => $ok]);

        return response()->json([
            'result' => $ok ? 'ok' : 'fail',
            'message' => $message,
        ]);
    }
}
