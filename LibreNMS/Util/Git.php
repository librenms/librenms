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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use const false;
use LibreNMS\Config;
use Symfony\Component\Process\Process;

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

    public static function applyPatch($number)
    {
        $number = (int)$number; // make sure $number is an integer
        $url = "https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/$number.diff";
        $process = new Process('curl -s "$url" | git apply --exclude=*.png -v');
        $process->setTty(true);
        $process->run(['url' => $url]);
//    *) curl -s https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/${1}.diff | git apply --exclude=*.png -v ${2}
//       rm -f cache/os_defs.cache 2> /dev/null;;
//esac

    }

    private static function postPatch()
    {
        OSDefinition::updateCache(false);
    }
}
