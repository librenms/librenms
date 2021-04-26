<?php
/**
 * InstallationChecksController.php
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

use LibreNMS\Interfaces\InstallerStep;
use LibreNMS\Validations\Php;

class ChecksController extends InstallationController implements InstallerStep
{
    const MODULES = ['pdo_mysql', 'mysqlnd', 'gd'];
    protected $step = 'checks';

    public function index()
    {
        $this->initInstallStep();

        if ($this->complete()) {
            $this->markStepComplete();
        }

        preg_match('/\d+\.\d+\.\d+/', PHP_VERSION, $matches);
        $version = $matches[0] ?? PHP_VERSION;

        return view('install.checks', $this->formatData([
            'php_version' => $version,
            'php_required' => Php::PHP_MIN_VERSION,
            'php_ok' => $this->checkPhpVersion(),
            'modules' => $this->moduleResults(),
        ]));
    }

    private function moduleResults()
    {
        $results = [];

        foreach (self::MODULES as $module) {
            $status = extension_loaded($module);
            $results[] = [
                'name' => str_replace('install.checks.php_module.', '', trans('install.checks.php_module.' . $module)),
                'status' => $status,
            ];
        }

        return $results;
    }

    private function checkPhpVersion()
    {
        return version_compare(PHP_VERSION, Php::PHP_MIN_VERSION, '>=');
    }

    public function complete(): bool
    {
        if ($this->stepCompleted('checks')) {
            return true;
        }

        if (! $this->checkPhpVersion()) {
            return false;
        }

        foreach (self::MODULES as $module) {
            if (! extension_loaded($module)) {
                return false;
            }
        }

        return true;
    }

    public function enabled(): bool
    {
        return true;
    }

    public function icon(): string
    {
        return 'fa-list-ul fa-flip-horizontal';
    }
}
