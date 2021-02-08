<?php
/**
 * InstallationController.php
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

use App\Http\Controllers\Controller;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\InstallerStep;

class InstallationController extends Controller
{
    protected $connection = 'setup';
    protected $step;
    protected $steps = [
        'checks' => \App\Http\Controllers\Install\ChecksController::class,
        'database' => \App\Http\Controllers\Install\DatabaseController::class,
        'user' => \App\Http\Controllers\Install\MakeUserController::class,
        'finish' => \App\Http\Controllers\Install\FinalizeController::class,
    ];

    public function redirectToFirst()
    {
        $step = collect($this->filterActiveSteps())->keys()->first(null, 'checks');

        return redirect()->route("install.$step");
    }

    public function redirectToIncomplete()
    {
        foreach ($this->filterActiveSteps() as $step => $controller) {
            /** @var InstallerStep $controller */
            if (! $controller->complete()) {
                return redirect()->route("install.$step");
            }
        }

        return redirect()->route('install.checks');
    }

    public function invalid()
    {
        abort(404);
    }

    public function stepsCompleted()
    {
        return response()->json($this->stepStatus());
    }

    /**
     * Init step info and return false if previous steps have not been completed.
     *
     * @return bool if all previous steps have been completed
     */
    final protected function initInstallStep()
    {
        $this->filterActiveSteps();
        $this->configureDatabase();

        foreach ($this->stepStatus() as $step => $status) {
            if ($step == $this->step) {
                return true;
            }

            if (! $status['complete']) {
                return false;
            }
        }

        return false;
    }

    final protected function markStepComplete()
    {
        if (! $this->stepCompleted($this->step)) {
            session(["install.$this->step" => true]);
            session()->save();
        }
    }

    final protected function stepCompleted(string $step)
    {
        return (bool) session("install.$step");
    }

    final protected function formatData($data = [])
    {
        $data['steps'] = $this->hydrateControllers();
        $data['step'] = $this->step;

        return $data;
    }

    protected function configureDatabase()
    {
        $db = session('db');
        if (! empty($db)) {
            Eloquent::setConnection(
                $this->connection,
                $db['host'] ?? 'localhost',
                $db['username'] ?? 'librenms',
                $db['password'] ?? null,
                $db['database'] ?? 'librenms',
                $db['port'] ?? 3306,
                $db['socket'] ?? null
            );
            config(['database.default', $this->connection]);
        }
    }

    protected function filterActiveSteps()
    {
        if (is_string(config('librenms.install'))) {
            $this->steps = array_intersect_key($this->steps, array_flip(explode(',', config('librenms.install'))));
        }

        return $this->steps;
    }

    protected function hydrateControllers()
    {
        $this->steps = array_map(function ($class) {
            return is_object($class) ? $class : app()->make($class);
        }, $this->steps);

        return $this->steps;
    }

    private function stepStatus()
    {
        $this->hydrateControllers();

        return array_map(function (InstallerStep $controller) {
            return [
                'enabled' => $controller->enabled(),
                'complete' => $controller->complete(),
            ];
        }, $this->steps);
    }
}
