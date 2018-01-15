<?php
/**
 * ComposerHelper.php
 *
 * Helper functions for composer
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class ComposerHelper
{
    public static function postRootPackageInstall(Event $event)
    {
        self::populateEnv();
    }

    public static function postInstall(Event $event)
    {
        self::populateEnv();
    }

    public static function preUpdate(Event $event)
    {
        if (!getenv('FORCE')) {
            echo "Running composer update is not advisable.  Please run composer install to update instead.\n";
            echo "If know what you are doing and want to write a new composer.lock file set FORCE=1.\n";
            echo "If you don't know what to do, run: composer install\n";
            exit(1);
        }
    }

    public static function preInstall(Event $event)
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

        if (!is_file("$vendor_dir/autoload.php")) {
            // checkout vendor from 1.36
            $cmds = array(
                "git checkout 609676a9f8d72da081c61f82967e1d16defc0c4e -- $vendor_dir",
                "git reset HEAD $vendor_dir"  // don't add vendor directory to the index
            );

            self::exec($cmds);
        }
    }


    /**
     * Initially populate .env file
     */
    private static function populateEnv()
    {
        if (!file_exists('.env')) {
            copy('.env.example', '.env');
            self::exec('php artisan key:generate');

            @include 'config.php';
            if (isset($config)) {
                self::setEnv([
                    'DB_HOST'     => isset($config['db_host']) ? $config['db_host'] : '',
                    'DB_PORT'     => isset($config['db_port']) ? $config['db_port'] : '',
                    'DB_USERNAME' => isset($config['db_user']) ? $config['db_user'] : '',
                    'DB_PASSWORD' => isset($config['db_pass']) ? $config['db_pass'] : '',
                    'DB_DATABASE' => isset($config['db_name']) ? $config['db_name'] : '',
                    'DB_SOCKET'   => isset($config['db_socket']) ? $config['db_socket'] : '',
                ]);
            }
        }
    }

    /**
     * Set a setting in .env file
     *
     * @param array $settings KEY => value list of settings
     * @param string $file
     */
    private static function setEnv($settings, $file = '.env')
    {
        $content = file_get_contents($file);
        if (substr($content, -1) !== "\n") {
            $content .= PHP_EOL;
        }

        foreach ($settings as $key => $value) {
            if (strpos($content, "$key=") !== false) {
                // only replace ones that aren't already set for safety
                $content = preg_replace("/$key=\n/", "$key=$value\n", $content);
            } elseif (!empty($value)) {
                // only add non-empty settings
                $content .= "$key=$value\n";
            }
        }

        file_put_contents($file, $content);
    }

    /**
     * Run a command or array of commands and echo the command and output
     *
     * @param string|array $cmds
     */
    private static function exec($cmds)
    {
        $cmd = "set -v\n" . implode(PHP_EOL, (array)$cmds);
        passthru($cmd);
    }
}
