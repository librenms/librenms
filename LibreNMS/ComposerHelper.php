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
        if (!$event->isDevMode()) {
            $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

            $cmds = array(
                "git checkout $vendor_dir",
                "git clean -x -d -f $vendor_dir"
            );

            self::exec($cmds);
        }
    }

    public static function postAutoloadDump(Event $event)
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

        $no = $event->isDevMode() ? '' : 'no-';
        $cmd = "git ls-files -z $vendor_dir | xargs -0 git update-index --{$no}assume-unchanged";

        self::exec($cmd);
    }

    public static function commit(Event $event)
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');
        $composer_json_path = $event->getComposer()->getConfig()->getConfigSource()->getName();
        $cmds = array(
            "git add -f $vendor_dir $composer_json_path",
            "git commit"
        );

        self::exec($cmds);
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
