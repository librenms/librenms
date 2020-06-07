<?php
/**
 * DatabaseMigrationController.php
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

use App\Http\Controllers\Controller;
use App\StreamedOutput;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseMigrationController extends Controller
{
    use UsesDatabase;

    public function __invoke()
    {
        return view('install.migrate-database', ['stage' => 4]);
    }

    public function migrate(Request $request)
    {
        $this->setDB();

        $response = new StreamedResponse(function () {
            try {
                $output = new StreamedOutput(fopen('php://stdout', 'w'));
                echo "Starting Update...\n";
                $ret = \Artisan::call('migrate', ['--seed' => true, '--force' => true, '--database' => $this->connection], $output);
                if ($ret !== 0) {
                    throw new \RuntimeException('Migration failed');
                }
                echo "\n\nSuccess!";
                session(['install.user' => true]);
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n\nError!";
            }
        });

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
