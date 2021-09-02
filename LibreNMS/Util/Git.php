<?php
/**
 * Git.php
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

namespace LibreNMS\Util;

use LibreNMS\Config;

class Git
{
    public static function repoPresent()
    {
        $install_dir = Config::get('install_dir', realpath(__DIR__ . '/../..'));

        return file_exists("$install_dir/.git");
    }

    public static function binaryExists()
    {
        exec('git > /dev/null 2>&1', $response, $exit_code);

        return $exit_code === 1;
    }
}
