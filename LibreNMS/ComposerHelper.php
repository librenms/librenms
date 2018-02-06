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

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class ComposerHelper
{
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
