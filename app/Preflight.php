<?php
/**
 * Preflight.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App;

use Illuminate\Support\Facades\App;
use LibreNMS\Config;

class Preflight
{
    public static function checkAll()
    {
        Config::load(); // load without database

        self::checkWriteAccess();
    }

    public static function checkWriteAccess()
    {
        // check file/folder permissions
        $check = [
            base_path('bootstrap/cache'),
            base_path('storage'),
            base_path('logs/librenms.log')
        ];
        foreach ($check as $path) {
            if (!is_writable($path)) {
                $user = Config::get('user', 'librenms');
                $group = Config::get('group', $user);
                $dirs = 'rrd/ logs/ storage/ bootstrap/cache/';
                self::printMessage(
                    "Error: $path is not writable! Run these commands to fix:",
                    [
                        "cd " . base_path(),
                        "chown -R $user:$group $dirs",
                        "setfacl -R -m g::rwx $dirs",
                        "setfacl -d -m g::rwx $dirs"
                    ]
                );
                exit;
            }
        }
    }


    private static function printMessage($title, $content)
    {
        $content = array_wrap($content);

        if (PHP_SAPI == 'cli') {
            $format = "%s\n\n%s\n\n";
            $message = implode(PHP_EOL, $content);
        } else {
            $format = "<h3 style='color: firebrick;'>%s</h3><p>%s</p>";
            $message = implode('<br />', $content);
        }

        printf($format, $title, $message);
    }
}
