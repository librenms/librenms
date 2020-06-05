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

class ChecksController extends \App\Http\Controllers\Controller
{
    public function __invoke()
    {
        $checks = [
            ['item' => 'test', 'status' => false, 'comment' => 'comment'],
            $this->checkPhpModule('pdo_mysql'),
            $this->checkPhpModule('mysqlnd'),
            $this->checkPhpModule('gd'),
        ];

        return view('install.checks', ['stage' => 1, 'checks' => $checks]);
    }

    private function checkPhpModule($module)
    {
        return [
            'item' => trans('install.checks.php_module', ['module' => $module]),
            'status' => extension_loaded("$module"),
        ];
    }
}
