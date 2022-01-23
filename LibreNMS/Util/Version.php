<?php
/**
 * Version.php
 *
 * Get version info about LibreNMS and various components/dependencies
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

use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use Symfony\Component\Process\Process;

class Version
{
    // Update this on release
    const VERSION = '22.1.0';

    /**
     * @var bool
     */
    protected $is_git_install = false;

    public function __construct()
    {
        $this->is_git_install = Git::repoPresent() && Git::binaryExists();
    }

    public static function get(): Version
    {
        return new static;
    }

    public function local(): string
    {
        if ($this->is_git_install && $version = $this->fromGit()) {
            return $version;
        }

        return self::VERSION;
    }

    public function database(): array
    {
        if (Eloquent::isConnected()) {
            try {
                $query = Eloquent::DB()->table('migrations');

                return [
                    'last' => $query->orderBy('id', 'desc')->value('migration'),
                    'total' => $query->count(),
                ];
            } catch (\Exception $e) {
                return ['last' => 'No Schema', 'total' => 0];
            }
        }

        return ['last' => 'Not Connected', 'total' => 0];
    }

    private function fromGit(): string
    {
        return rtrim(shell_exec('git describe --tags 2>/dev/null'));
    }

    public function gitChangelog(): string
    {
        return $this->is_git_install
            ? rtrim(shell_exec('git log -10'))
            : '';
    }

    public function gitDate(): string
    {
        return $this->is_git_install
            ? rtrim(shell_exec("git show --pretty='%ct' -s HEAD"))
            : '';
    }

    public function python(): string
    {
        $proc = new Process(['python3', '--version']);
        $proc->run();

        if ($proc->getExitCode() !== 0) {
            return '';
        }

        return explode(' ', rtrim($proc->getOutput()), 2)[1] ?? '';
    }

    public function rrdtool(): string
    {
        $process = new Process([Config::get('rrdtool', 'rrdtool'), '--version']);
        $process->run();
        preg_match('/^RRDtool ([\w.]+) /', $process->getOutput(), $matches);

        return str_replace('1.7.01.7.0', '1.7.0', $matches[1] ?? '');
    }

    public function netSnmp(): string
    {
        $process = new Process([Config::get('snmpget', 'snmpget'), '-V']);
        $process->run();
        preg_match('/[\w.]+$/', $process->getErrorOutput(), $matches);

        return $matches[0] ?? '';
    }
}
