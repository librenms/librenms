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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use App\StreamedOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\Interfaces\InstallerStep;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Database;
use LibreNMS\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseController extends InstallationController implements InstallerStep
{
    const KEYS = ['host', 'username', 'password', 'database', 'port', 'unix_socket'];
    protected $step = 'database';

    public function index(Request $request)
    {
        if (! $this->initInstallStep()) {
            return $this->redirectToIncomplete();
        }

        $data = Arr::only(session()->get('db') ?: [], self::KEYS);
        $data['valid_credentials'] = Eloquent::isConnected();
        $data['migrated'] = session('install.database');

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
        session()->forget('install.database'); // reset db complete status

        $ok = false;
        $messages = [];
        try {
            $conn = Eloquent::DB('setup');
            $ok = $conn && ! is_null($conn->getPdo());

            // validate Database
            $validator = new Validator();
            (new Database())->validateSystem($validator);
            $results = $validator->getResults('database');

            foreach ($results as $result) {
                if ($result->getStatus() == ValidationResult::FAILURE) {
                    $ok = false;
                    $messages[] = $result->getMessage() . '  ' . $result->getFix();
                }
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }

        return response()->json([
            'result' => $ok ? 'ok' : 'fail',
            'message' => implode('<br />', $messages),
        ]);
    }

    public function migrate(Request $request)
    {
        $response = new StreamedResponse(function () {
            try {
                $this->configureDatabase();
                $output = new StreamedOutput(fopen('php://stdout', 'w'));
                echo "Starting Update...\n";
                $ret = \Artisan::call('migrate', ['--seed' => true, '--force' => true], $output);
                if ($ret !== 0) {
                    throw new \RuntimeException('Migration failed');
                }
                echo "\n\nSuccess!";
                $this->markStepComplete();
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
        if ($this->stepCompleted('database')) {
            return true;
        }

        $this->configureDatabase();
        if (Eloquent::isConnected() && Schema::isCurrent()) {
            $this->markStepComplete();

            return true;
        }

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
