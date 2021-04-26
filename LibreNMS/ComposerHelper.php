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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Composer\Script\Event;
use LibreNMS\Exceptions\FileWriteFailedException;
use LibreNMS\Util\EnvHelper;

class ComposerHelper
{
    public static function postRootPackageInstall(Event $event)
    {
        if (! file_exists('.env')) {
            self::setPermissions();
            self::populateEnv();
        }
    }

    public static function postInstall(Event $event)
    {
        if (! file_exists('.env')) {
            self::setPermissions();
        }

        self::populateEnv();
    }

    public static function preUpdate(Event $event)
    {
        if (! getenv('FORCE')) {
            echo "Running composer update is not advisable.  Please run composer install to update instead.\n";
            echo "If know what you are doing and want to write a new composer.lock file set FORCE=1.\n";
            echo "If you don't know what to do, run: composer install\n";
            exit(1);
        }
    }

    public static function preInstall(Event $event)
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

        if (! is_file("$vendor_dir/autoload.php")) {
            // checkout vendor from 1.36
            $cmds = [
                "git checkout 609676a9f8d72da081c61f82967e1d16defc0c4e -- $vendor_dir",
                "git reset HEAD $vendor_dir",  // don't add vendor directory to the index
            ];

            self::exec($cmds);
        }
    }

    /**
     * Initially populate .env file
     */
    private static function populateEnv()
    {
        $config = [
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_pass' => '',
            'db_socket' => '',
            'base_url' => '',
            'user' => '',
            'group' => '',
        ];

        @include 'config.php';

        try {
            EnvHelper::init();
            EnvHelper::writeEnv([
                'NODE_ID' => uniqid(),
                'DB_HOST' => $config['db_host'],
                'DB_PORT' => $config['db_port'],
                'DB_USERNAME' => $config['db_user'],
                'DB_PASSWORD' => $config['db_pass'],
                'DB_DATABASE' => $config['db_name'],
                'DB_SOCKET' => $config['db_socket'],
                'APP_URL' => $config['base_url'],
                'LIBRENMS_USER' => $config['user'],
                'LIBRENMS_GROUP' => $config['group'],
            ]);
        } catch (FileWriteFailedException $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }

    private static function setPermissions()
    {
        $permissions_cmds = [
            'setfacl -R -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
            'setfacl -d -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
        ];

        self::exec($permissions_cmds);
    }

    /**
     * Run a command or array of commands and echo the command and output
     *
     * @param string|array $cmds
     */
    private static function exec($cmds)
    {
        $cmd = "set -v\n" . implode(PHP_EOL, (array) $cmds);
        passthru($cmd);
    }
}
