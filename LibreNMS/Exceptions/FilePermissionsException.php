<?php
/**
 * FilePermissionsException.php
 *
 * Required folders/files aren't writable
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

namespace LibreNMS\Exceptions;

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Interfaces\Exceptions\UpgradeableException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FilePermissionsException extends \Exception implements UpgradeableException
{
    /**
     * Try to convert the given Exception to a FilePermissionsException
     *
     * @param \Exception $exception
     * @return static|null
     */
    public static function upgrade($exception)
    {
        // cannot write to storage directory
        if ($exception instanceof \ErrorException &&
            Str::startsWith($exception->getMessage(), 'file_put_contents(') &&
            Str::contains($exception->getMessage(), '/storage/')) {
            return new static();
        }

        // cannot write to bootstrap directory
        if ($exception instanceof \Exception && $exception->getMessage() == 'The bootstrap/cache directory must be present and writable.') {
            return new static();
        }

        // monolog cannot init log file
        if ($exception instanceof \UnexpectedValueException && Str::contains($exception->getFile(), 'Monolog/Handler/StreamHandler.php')) {
            return new static();
        }

        return null;
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $log_file = config('app.log') ?: Config::get('log_file', base_path('logs/librenms.log'));
        $commands = $this->generateCommands($log_file);

        // use pre-compiled template because we probably can't compile it.
        $template = file_get_contents(base_path('resources/views/errors/static/file_permissions.html'));
        $content = str_replace('!!!!CONTENT!!!!', '<p>' . implode('</p><p>', $commands) . '</p>', $template);
        $content = str_replace('!!!!LOG_FILE!!!!', $log_file, $content);

        return SymfonyResponse::create($content);
    }

    /**
     * @param string $log_file
     * @return array
     */
    private function generateCommands($log_file): array
    {
        $user = config('librenms.user');
        $group = config('librenms.group');
        $install_dir = base_path();
        $commands = [];
        $dirs = [
            base_path('bootstrap/cache'),
            base_path('storage'),
            Config::get('log_dir', base_path('logs')),
            Config::get('rrd_dir', base_path('rrd')),
        ];

        // check if folders are missing
        $mkdirs = [
            base_path('bootstrap/cache'),
            base_path('storage/framework/sessions'),
            base_path('storage/framework/views'),
            base_path('storage/framework/cache'),
            Config::get('log_dir', base_path('logs')),
            Config::get('rrd_dir', base_path('rrd')),
        ];

        $mk_dirs = array_filter($mkdirs, function ($file) {
            return ! file_exists($file);
        });

        if (! empty($mk_dirs)) {
            $commands[] = 'sudo mkdir -p ' . implode(' ', $mk_dirs);
        }

        // always print chwon/setfacl/chmod commands
        $commands[] = "sudo chown -R $user:$group $install_dir";
        $commands[] = 'sudo setfacl -d -m g::rwx ' . implode(' ', $dirs);
        $commands[] = 'sudo chmod -R ug=rwX ' . implode(' ', $dirs);

        // check if webserver is in the librenms group
        $current_groups = explode(' ', trim(exec('groups')));
        if (! in_array($group, $current_groups)) {
            $current_user = trim(exec('whoami'));
            $commands[] = "usermod -a -G $group $current_user";
        }

        // check for invalid log setting
        if (! is_file($log_file) || ! is_writable($log_file)) {
            // override for proper error output
            $dirs = [$log_file];
            $install_dir = $log_file;
            $commands = [
                '<h3>Cannot write to log file: &quot;' . $log_file . '&quot;</h3>',
                'Make sure it exists and is writable, or change your LOG_DIR setting.',
            ];
        }

        // selinux:
        $commands[] = '<h4>If using SELinux you may also need:</h4>';
        foreach ($dirs as $dir) {
            $commands[] = "semanage fcontext -a -t httpd_sys_rw_content_t '$dir(/.*)?'";
        }
        $commands[] = "restorecon -RFv $install_dir";

        return $commands;
    }
}
