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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App;

use App\Models\Device;
use App\Models\Notification;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use Toastr;

class Checks
{
    /**
     * Post boot Toast messages
     */
    public static function postAuth()
    {
        // limit popup messages frequency
        if (Cache::get('checks_popup_timeout') || ! Auth::check()) {
            return;
        }

        Cache::put('checks_popup_timeout', true, Config::get('checks_popup_timer', 5) * 60);

        $user = Auth::user();

        if ($user->isAdmin()) {
            $notifications = Notification::isUnread($user)->where('severity', '>', \LibreNMS\Enum\Alert::OK)->get();
            foreach ($notifications as $notification) {
                Toastr::error("<a href='notifications/'>$notification->body</a>", $notification->title);
            }

            $warn_sec = Config::get('rrd.step', 300) * 3;
            if (Device::isUp()->where('last_polled', '<=', Carbon::now()->subSeconds($warn_sec))->exists()) {
                $warn_min = $warn_sec / 60;
                Toastr::warning('<a href="poller/log?filter=unpolled/">It appears as though you have some devices that haven\'t completed polling within the last ' . $warn_min . ' minutes, you may want to check that out :)</a>', 'Devices unpolled');
            }

            // Directory access checks
            $rrd_dir = Config::get('rrd_dir');
            if (! is_dir($rrd_dir)) {
                Toastr::error("RRD Directory is missing ($rrd_dir).  Graphing may fail. <a href=" . url('validate') . '>Validate your install</a>');
            }

            $temp_dir = Config::get('temp_dir');
            if (! is_dir($temp_dir)) {
                Toastr::error("Temp Directory is missing ($temp_dir).  Graphing may fail. <a href=" . url('validate') . '>Validate your install</a>');
            } elseif (! is_writable($temp_dir)) {
                Toastr::error("Temp Directory is not writable ($temp_dir).  Graphing may fail. <a href='" . url('validate') . "'>Validate your install</a>");
            }
        }
    }

    /**
     * Check the script is running as the right user (works before config is available)
     */
    public static function runningUser()
    {
        if (function_exists('posix_getpwuid') && posix_getpwuid(posix_geteuid())['name'] !== get_current_user()) {
            if (get_current_user() == 'root') {
                self::printMessage(
                    'Error: lnms file is owned by root, it should be owned and ran by a non-privileged user.',
                    null,
                    true
                );
            }

            self::printMessage(
                'Error: You must run lnms as the user ' . get_current_user(),
                null,
                true
            );
        }
    }

    private static function printMessage($title, $content, $exit = false)
    {
        $content = (array) $content;

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
}
