<?php
/**
 * Git.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\ComposerHelper;
use LibreNMS\Config;
use Symfony\Component\Process\Process;

class Git
{
    public static function repoPresent()
    {
        $install_dir = Config::get('install_dir', realpath(__DIR__ . '/../..'));
        return file_exists("$install_dir/.git");
    }

    public static function binaryExists()
    {
        exec('git > /dev/null 2>&1', $response, $exit_code);
        return $exit_code === 1;
    }

    /**
     * Apply a pull request patch directly from GitHub
     *
     * @param int $number The PR number from GitHub
     * @param bool $reverse Reverse the patch
     * @return Process
     */
    public static function applyPullRequest($number, $reverse = false)
    {
        $number = (int)$number; // make sure $number is an integer
        $url = "https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/$number.diff";
        $command = 'curl -s "$URL" | git apply --exclude=*.png --verbose';

        if ($reverse) {
            $command .= ' --reverse';
        }

        $process = new Process($command, Config::installDir(), ['URL' => $url]);
        $process->run();

        if ($process->getExitCode() == 0) {
            self::postPatch();
        }

        return $process;
    }

    /**
     * Clean files and try to reset LibreNMS back to a pristine state
     *
     * @param bool $vendor
     */
    public static function clean($vendor = false)
    {

        $dirs = [
            "app/",
            "bootstrap/",
            "contrib/",
            "database/",
            "doc/",
            "html/",
            "includes/",
            "LibreNMS/",
            "licenses/",
            "mibs/",
            "misc/",
            "resources/",
            "routes",
            "scripts/",
            "sql-schema/",
            "tests/"
        ];
        $gitignores = [
            '.gitignore',
            'bootstrap/cache/.gitignore',
            'logs/.gitignore',
            'rrd/.gitignore',
            'storage/app/.gitignore',
            'storage/app/public/.gitignore',
            'storage/debugbar/.gitignore',
            'storage/framework/cache/.gitignore',
            'storage/framework/sessions/.gitignore',
            'storage/framework/testing/.gitignore',
            'storage/framework/views/.gitignore',
            'storage/logs/.gitignore'
        ];

        (new Process(["git", "reset", "-q"], Config::installDir()))->run();
        (new Process(["git", "clean", "-d", "-f"] + $dirs, Config::installDir()))->run();

        //fix messed up gitignore file modes
        (new Process(["git", "checkout"] + $gitignores, Config::installDir()))->run();

        if ($vendor) {
            (new Process(["git", "clean",  "-x",  "-d",  "-f",  "vendor/"], Config::installDir()))->run();
        }

        self::postPatch();
    }

    private static function postPatch()
    {
        ComposerHelper::install(false);
        OSDefinition::updateCache(true);
    }
}
