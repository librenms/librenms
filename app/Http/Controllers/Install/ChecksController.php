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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use LibreNMS\Validations\Php;

class ChecksController extends \App\Http\Controllers\Controller
{
    public function __invoke()
    {
        $results = [];
        $php_ok = version_compare(PHP_VERSION, Php::PHP_MIN_VERSION, '>=');

        // bitwise and so all checks run
        if ($php_ok
            & $this->checkPhpModule($results, 'pdo_mysql')
            & $this->checkPhpModule($results, 'mysqlnd')
            & $this->checkPhpModule($results, 'gd')
        ) {
            session(['install.checks' => true]);
        }

        return view('install.checks', [
            'php_version' => PHP_VERSION,
            'php_required' => Php::PHP_MIN_VERSION,
            'php_ok' => $php_ok,
            'modules' => $results
        ]);
    }

    private function checkPhpModule(&$results, $module)
    {
        $status = extension_loaded("$module");
        $results[] = [
            'name' => str_replace('install.checks.php_module.', '', trans('install.checks.php_module.' . $module)),
            'status' => $status,
        ];

        return $status;
    }

    public static function enabled(): bool
    {
        return true;
    }

    public static function icon(): string
    {
        return 'fa-list-ul fa-flip-horizontal';
    }
}
