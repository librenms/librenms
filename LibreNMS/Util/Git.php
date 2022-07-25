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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Carbon\Carbon;
use LibreNMS\Config;
use Symfony\Component\Process\Process;

class Git
{
    public static function repoPresent(): bool
    {
        $install_dir = Config::get('install_dir', realpath(__DIR__ . '/../..'));

        return file_exists("$install_dir/.git");
    }

    public static function binaryExists(): bool
    {
        exec('git > /dev/null 2>&1', $response, $exit_code);

        return $exit_code === 1;
    }

    public static function localCommit(): string
    {
        return rtrim(exec("git show --pretty='%H' -s HEAD"));
    }

    public static function localDate(): Carbon
    {
        return \Date::createFromTimestamp(exec("git show --pretty='%ct' -s HEAD"));
    }

    public static function unchanged(): bool
    {
        $process = new Process(['git', 'diff-index', '--quiet', 'HEAD']);
        $process->disableOutput();
        $process->run();

        return $process->getExitCode() === 0;
    }

    /**
     * Note: It assumes origin/master points to github.com/librenms/librenms for this to work.
     */
    public static function officalCommit(?string $hash = null, string $remote = "origin/master"): bool
    {
        if ($hash === null) {
            $process = new Process(['git', 'rev-parse', 'HEAD']);
            $process->run();

            $hash = trim($process->getOutput());
        }

        $process = new Process(['git', 'branch', '--remotes', '--contains', $hash, $remote]);
        $process->run();

        if ($process->isSuccessful()) {
            if (trim($process->getOutput()) == $remote) {
                return true;
            }
        }

        return false;
    }

    public static function remoteUrl(string $remote = 'origin'): string
    {
        $process = new Process(['git', 'ls-remote', '--get-url', $remote]);
        $process->run();

        return trim($process->getOutput());
    }
}
