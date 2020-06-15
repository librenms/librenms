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

use App\StreamedOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\InstallerStep;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseController extends InstallationController implements InstallerStep
{
    const KEYS = ['host', 'username', 'password', 'database', 'port', 'unix_socket'];

    public function index(Request $request)
    {
        $data = Arr::only(session()->get('db') ?: [], self::KEYS);
        $data['status'] = session('install.database');

        return view('install.database', $this->formatData($data));
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
            $conn = Eloquent::DB('setup');
            $ok = $conn && !is_null($conn->getPdo());
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        session(['install.database' => $ok]);

        return response()->json([
            'result' => $ok ? 'ok' : 'fail',
            'message' => $message,
        ]);
    }

    public function migrate(Request $request)
    {
        $response = new StreamedResponse(function () use ($request) {
            try {
                $this->configureDatabase();
                $output = new StreamedOutput(fopen('php://stdout', 'w'));
                echo "Starting Update...\n";
                $ret = \Artisan::call('migrate', ['--seed' => true, '--force' => true, '--database' => $this->connection], $output);
                if ($ret !== 0) {
                    throw new \RuntimeException('Migration failed');
                }
                echo "\n\nSuccess!";
                session(['install.migrate' => true]);
                session()->save();
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n\nError!";
            }
        });

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function complete(): bool
    {
        return false;
    }

    public function enabled(): bool
    {
        return true;
    }

    public function icon(): string
    {
        return 'fa-database';
    }
}
