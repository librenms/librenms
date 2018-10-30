<?php
/**
 * Php.php
 *
 * Check that various PHP modules and functions exist.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Php extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        $this->checkVersion($validator);
        $this->checkSessionDirWritable($validator);
        $this->checkExtensions($validator);
        $this->checkFunctions($validator);
        $this->checkTimezone($validator);
    }

    private function checkVersion(Validator$validator)
    {
        $min_version = '5.6.4';

        // if update is not set to false and version is min or newer
        if (Config::get('update') && version_compare(PHP_VERSION, $min_version, '<')) {
            $validator->warn('PHP version 5.6.4 is the minimum supported version as of January 10, 2018.  We recommend you update to PHP a supported version of PHP (7.1 suggested) to continue to receive updates.  If you do not update PHP, LibreNMS will continue to function but stop receiving bug fixes and updates.');
        }
    }

    private function checkSessionDirWritable(Validator $validator)
    {
        $path = session_save_path() === '' ? '/tmp' : session_save_path();
        if (!is_writable($path)) {
            $result = ValidationResult::fail("The session directory ($path) is not writable.");

            $group_id = filegroup($path);
            if ($group_id !== 0 && check_file_permissions($path, '060')) {
                // don't suggest adding users to the root group or a group that doesn't have write permission.
                if (function_exists('posix_getgrgid')) {
                    $group_info = posix_getgrgid($group_id);
                    $group = $group_info['name'];
                    $user = $validator->getUsername();
                    $result->setFix("usermod -a -G $group $user");
                }
            }

            $validator->result($result);
        }
    }

    private function checkExtensions(Validator $validator)
    {
        $required_modules = ['mysqlnd', 'mbstring', 'pcre', 'curl', 'session', 'xml', 'gd'];

        if (Config::get('distributed_poller')) {
            $required_modules[] = 'memcached';
        }

        foreach ($required_modules as $extension) {
            if (!extension_loaded($extension)) {
                $validator->fail("Missing PHP extension: $extension", "Please install $extension");
            } elseif (shell_exec("php -r \"var_export(extension_loaded('$extension'));\"") == 'false') {
                $validator->fail("Missing CLI PHP extension: $extension", "Please install $extension");
            }
        }

        $suggested_extensions = array('posix' => 'php-process');
        foreach ($suggested_extensions as $extension => $packages) {
            if (!extension_loaded($extension)) {
                $validator->warn("Missing optional PHP extension: $extension", "It is suggested you install $packages or the one that matches your php version");
            }
        }
    }

    private function checkFunctions(Validator $validator)
    {
        $disabled_functions = explode(',', ini_get('disable_functions'));
        $required_functions = array(
            'exec',
            'passthru',
            'shell_exec',
            'escapeshellarg',
            'escapeshellcmd',
            'proc_close',
            'proc_open',
            'popen'
        );

        foreach ($required_functions as $function) {
            if (in_array($function, $disabled_functions)) {
                $validator->fail("$function is disabled in php.ini");
            }
        }

        if (!function_exists('openssl_random_pseudo_bytes')) {
            $validator->warn("openssl_random_pseudo_bytes is not being used for user password hashing. This is a recommended function (https://secure.php.net/openssl_random_pseudo_bytes)");
            if (!is_readable('/dev/urandom')) {
                $validator->warn("It also looks like we can't use /dev/urandom for user password hashing. We will fall back to generating our own hash - be warned");
            }
        }
    }

    private function checkTimezone(Validator $validator)
    {
        // collect data
        $ini_tz = ini_get('date.timezone');
        $sh_tz = rtrim(shell_exec('date +%Z'));
        $php_tz = date('T');
        $php_cli_tz = rtrim(shell_exec('php -r "echo date(\'T\');"'));

        if (empty($ini_tz)) {
            // make sure timezone is set
            $validator->fail(
                'You have no timezone set for php.',
                'http://php.net/manual/en/datetime.configuration.php#ini.date.timezone'
            );
        } elseif ($sh_tz !== $php_tz) {
            // check if system timezone matches the timezone of the current running php
            $validator->fail(
                "You have a different system timezone ($sh_tz) than the php configured timezone ($php_tz)",
                "Please correct either your system timezone or your timezone set in php.ini."
            );
        } elseif ($php_tz !== $php_cli_tz) {
            // check if web and cli timezones match (this does nothing if validate.php is run on cli)
            // some distros have different php.ini for cli and the web server
            if ($sh_tz !== $php_cli_tz) {
                $validator->fail(
                    "The CLI php.ini ($php_cli_tz) timezone is different than your system's timezone ($sh_tz)",
                    "Edit your CLI php.ini file and set the correct timezone ($sh_tz)."
                );
            }
        }
    }
}
