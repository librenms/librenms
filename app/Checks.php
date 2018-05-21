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
use Carbon\Carbon;
use Dotenv\Dotenv;
use Kamaln7\Toastr\Facades\Toastr;
use LibreNMS\Config;

class Checks
{
    public static function preBoot()
    {
        // check php extensions
        $missing = self::missingPhpExtensions();

        if (!empty($missing)) {
            self::printMessage(
                "Missing PHP extensions.  Please install and enable them on your LibreNMS server.",
                $missing,
                true
            );
        }

        // check file/folder permissions
        $check_folders = [
            self::basePath('bootstrap/cache'),
            self::basePath('storage'),
            self::basePath('storage/framework/sessions'),
            self::basePath('storage/framework/views'),
            self::basePath('storage/framework/cache'),
            self::basePath('logs'),
        ];

        $check_files = [
            self::basePath('logs/librenms.log'), // This file is important because Laravel needs to be able to write to it
        ];

        // check that each is writable
        $check_folders = array_filter($check_folders, function ($path) {
            return !is_writable($path);
        });

        $check_files = array_filter($check_files, function ($path) {
            return file_exists($path) xor is_writable($path);
        });

        if (!empty($check_folders) || !empty($check_files)) {
            // only operate on parent directories, not files
            $check = array_unique(array_merge($check_folders, array_map('dirname', $check_files)));

            // load .env, it isn't loaded
            $dotenv = new Dotenv(__DIR__ . '/../');
            $dotenv->load();

            $user = env('LIBRENMS_USER', 'librenms');
            $group = env('LIBRENMS_GROUP', $user);

            // build chown message
            $dirs = implode(' ', $check);
            $chown_commands =                 [
                "chown -R $user:$group $dirs",
                "setfacl -R -m g::rwx $dirs",
                "setfacl -d -m g::rwx $dirs",
            ];

            $current_groups = explode(' ', trim(exec('groups')));
            if (!in_array($group, $current_groups)) {
                $current_user = trim(exec('whoami'));
                $chown_commands[] = "usermod -a -G $group $current_user";
            }


            //check for missing directories
            $missing = array_filter($check, function ($file) {
                return !file_exists($file);
            });

            if (!empty($missing)) {
                array_unshift($chown_commands, 'mkdir -p ' . implode(' ', $missing));
            }

            $short_dirs = implode(', ', array_map(function ($dir) {
                return str_replace(self::basePath(), '', $dir);
            }, $check));

            self::printMessage(
                "Error: $short_dirs not writable! Run these commands as root on your LibreNMS server to fix:",
                $chown_commands
            );

            // build SELinux output
            $selinux_commands = [];
            foreach ($check as $dir) {
                $selinux_commands[] = "semanage fcontext -a -t httpd_sys_content_t '$dir(/.*)?'";
                $selinux_commands[] = "semanage fcontext -a -t httpd_sys_rw_content_t '$dir(/.*)?'";
                $selinux_commands[] = "restorecon -RFvv $dir";
            }

            self::printMessage(
                "If using SELinux you may also need:",
                $selinux_commands,
                true
            );
        }
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

    /**
     * Post boot Toast messages
     */
    public static function postAuth()
    {
        $notifications = Notification::isUnread(Auth::user())->where('severity', '>', 1)->get();
        foreach ($notifications as $notification) {
            Toastr::error("<a href='notifications/'>$notification->body</a>", $notification->title);
        }

        if (Device::isUp()->whereTime('last_polled', '<=', Carbon::now()->subMinutes(15))->count() > 0) {
            Toastr::warning('<a href="poll-log/filter=unpolled/">It appears as though you have some devices that haven\'t completed polling within the last 15 minutes, you may want to check that out :)</a>', 'Devices unpolled');
        }

        // Directory access checks
        $rrd_dir = Config::get('rrd_dir');
        if (!is_dir($rrd_dir)) {
            Toastr::error("RRD Directory is missing ($rrd_dir).  Graphing may fail.");
        }

        $temp_dir = Config::get('temp_dir');
        if (!is_dir($temp_dir)) {
            Toastr::error("Temp Directory is missing ($temp_dir).  Graphing may fail.");
        } elseif (!is_writable($temp_dir)) {
            Toastr::error("Temp Directory is not writable ($temp_dir).  Graphing may fail.");
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

    private static function basePath($path = '')
    {
        $base_dir = realpath(__DIR__ . '/..');
        return "$base_dir/$path";
    }

    private static function missingPhpExtensions()
    {
        $required_modules = ['mysqli', 'mbstring', 'pcre', 'curl', 'session', 'xml', 'gd'];

        return array_filter($required_modules, function ($module) {
            return !extension_loaded($module);
        });
    }
}
