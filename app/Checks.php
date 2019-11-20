<?php
/**
 * Checks.php
 *
 * Pre-flight checks at various stages of booting
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

use App\Models\Device;
use App\Models\Notification;
use Auth;
use Cache;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Exceptions\FilePermissionsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Toastr;

class Checks
{
    public static function preAutoload()
    {
        // Check PHP version otherwise it will just say server error
        if (version_compare('7.1.3', PHP_VERSION, '>=')) {
            self::printMessage(
                'PHP version 7.1.3 or newer is required to run LibreNMS',
                null,
                true
            );
        };
    }

    /**
     * Pre-boot dependency check
     */
    public static function postAutoload()
    {
        if (!class_exists(\Illuminate\Foundation\Application::class)) {
            self::printMessage(
                'Error: Missing dependencies! Run the following command to fix:',
                './scripts/composer_wrapper.php install --no-dev',
                true
            );
        }
    }

    public static function preBoot()
    {
        // check php extensions
        if ($missing = self::missingPhpExtensions()) {
            self::printMessage(
                "Missing PHP extensions.  Please install and enable them on your LibreNMS server.",
                $missing,
                true
            );
        }
    }

    /**
     * Post boot Toast messages
     */
    public static function postAuth()
    {
        // limit popup messages frequency
        if (Cache::get('checks_popup_timeout') || !Auth::check()) {
            return;
        }

        Cache::put('checks_popup_timeout', true, Config::get('checks_popup_timer', 5) * 60);

        $user = Auth::user();

        if ($user->isAdmin()) {
            $notifications = Notification::isUnread($user)->where('severity', '>', 1)->get();
            foreach ($notifications as $notification) {
                Toastr::error("<a href='notifications/'>$notification->body</a>", $notification->title);
            }

            $warn_sec = Config::get('rrd.step', 300) * 3;
            if (Device::isUp()->where('last_polled', '<=', Carbon::now()->subSeconds($warn_sec))->exists()) {
                $warn_min = $warn_sec / 60;
                Toastr::warning('<a href="poll-log/filter=unpolled/">It appears as though you have some devices that haven\'t completed polling within the last ' . $warn_min . ' minutes, you may want to check that out :)</a>', 'Devices unpolled');
            }

            // Directory access checks
            $rrd_dir = Config::get('rrd_dir');
            if (!is_dir($rrd_dir)) {
                Toastr::error("RRD Directory is missing ($rrd_dir).  Graphing may fail. <a href=" . url('validate') . ">Validate your install</a>");
            }

            $temp_dir = Config::get('temp_dir');
            if (!is_dir($temp_dir)) {
                Toastr::error("Temp Directory is missing ($temp_dir).  Graphing may fail. <a href=" . url('validate') . ">Validate your install</a>");
            } elseif (!is_writable($temp_dir)) {
                Toastr::error("Temp Directory is not writable ($temp_dir).  Graphing may fail. <a href='" . url('validate') . "'>Validate your install</a>");
            }
        }
    }

    private static function printMessage($title, $content, $exit = false)
    {
        $content = (array)$content;

        if (PHP_SAPI == 'cli') {
            $format = "%s\n\n%s\n\n";
            $message = implode(PHP_EOL, $content);
        } else {
            $format = "<h3 style='color: firebrick;'>%s</h3><p>%s</p>";
            $message = '';
            foreach ($content as $line) {
                $message .= "<p style='margin:0.5em'>$line</p>\n";
            }
        }

        printf($format, $title, $message);

        if ($exit) {
            exit(1);
        }
    }

    private static function missingPhpExtensions()
    {
        // allow mysqli, but prefer mysqlnd
        if (!extension_loaded('mysqlnd') && !extension_loaded('mysqli')) {
            return ['mysqlnd'];
        }

        $required_modules = ['mbstring', 'pcre', 'curl', 'session', 'xml', 'gd'];

        return array_filter($required_modules, function ($module) {
            return !extension_loaded($module);
        });
    }
}
