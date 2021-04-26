<?php
/**
 * LegacyApiController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Api\Controllers;

class LegacyApiController
{
    /**
     * Pass through api functions to api_functions.inc.php
     *
     * @param string $method_name
     * @param array $arguments
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        $init_modules = ['web', 'alerts'];
        require base_path('/includes/init.php');
        require_once base_path('includes/html/api_functions.inc.php');

        return app()->call($method_name, $arguments);
    }
}
