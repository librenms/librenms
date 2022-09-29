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

use DB;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use Symfony\Component\Process\Process;

class Version
{
    // Update this on release
    public const VERSION = '22.9.0';

    /** @var array */
    protected $cache = [];

    public static function get(): Version
    {
        try {
            return app()->make('version');
        } catch (BindingResolutionException $e) {
            return new static; // no container, just return a fresh instance
        }
    }

    public function release(): string
    {
        return Config::get('update_channel') == 'master' ? 'master' : self::VERSION;
    }

    public function local(): string
    {
        return $this->cacheGet('local_version', function () {
            if ($this->isGitInstall()) {
                $version = rtrim(shell_exec('git describe --tags 2>/dev/null'));
                if ($version) {
                    return $version;
                }
            }

            return self::VERSION;
        });
    }

    public function isGitInstall(): bool
    {
        return $this->cacheGet('install_type', function () {
            return (Git::repoPresent() && Git::binaryExists()) ? 'git' : 'other';
        }) == 'git';
    }

    /**
     * Compiles local commit data
     *
     * @return array with keys sha, date, and branch
     */
    public function localCommit(): array
    {
        return [
            'sha' => $this->localCommitSha(),
            'date' => $this->localDate(),
            'branch' => $this->localBranch(),
        ];
    }

    public function localCommitSha(): string
    {
        return $this->cacheGet('local_commit_sha', function () {
            if (! $this->isGitInstall()) {
                return '';
            }

            return $this->localCommitData()[0] ?? '';
        });
    }

    public function localDate(): string
    {
        return $this->cacheGet('local_commit_date', function () {
            return $this->localCommitData()[1] ?? '';
        });
    }

    public function localBranch(): string
    {
        return $this->cacheGet('local_branch', function () {
            if (! $this->isGitInstall()) {
                return '';
            }

            $branch_process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], Config::get('install_dir'));
            $branch_process->run();

            return rtrim($branch_process->getOutput());
        });
    }

    /**
     * Fetches the remote commit from the github api if on the daily release channel
     *
     * @return array
     */
    public function remoteCommit(): array
    {
        return json_decode($this->cacheGet('remote_commit', function () {
            if ($this->isGitInstall()) {
                try {
                    return \Http::withOptions(['proxy' => Proxy::forGuzzle()])->get(Config::get('github_api') . 'commits/master')->body();
                } catch (ConnectionException $e) {
                }
            }

            return '[]';
        }), true);
    }

    public function databaseServer(): string
    {
        if (! Eloquent::isConnected()) {
            return 'Not Connected';
        }

        switch (Eloquent::getDriver()) {
            case 'mysql':
                $ret = Arr::first(DB::selectOne('select version()'));

                return (str_contains($ret, 'MariaDB') ? 'MariaDB ' : 'MySQL ') . $ret;
            case 'sqlite':
                return 'SQLite ' . Arr::first(DB::selectOne('select sqlite_version()'));
            default:
                return 'Unsupported: ' . Eloquent::getDriver();
        }
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

    public function gitChangelog(): string
    {
        return $this->cacheGet('changelog', function () {
            return $this->isGitInstall()
                ? rtrim(shell_exec('git log -10'))
                : '';
        });
    }

    public function python(): string
    {
        return $this->cacheGet('python', function () {
            $proc = new Process(['python3', '--version']);
            $proc->run();

            if ($proc->getExitCode() !== 0) {
                return '';
            }

            return explode(' ', rtrim($proc->getOutput()), 2)[1] ?? '';
        });
    }

    public function rrdtool(): string
    {
        return $this->cacheGet('rrdtool', function () {
            $process = new Process([Config::get('rrdtool', 'rrdtool'), '--version']);
            $process->run();
            preg_match('/^RRDtool ([\w.]+) /', $process->getOutput(), $matches);

            return str_replace('1.7.01.7.0', '1.7.0', $matches[1] ?? '');
        });
    }

    public function netSnmp(): string
    {
        return $this->cacheGet('net-snmp', function () {
            $process = new Process([Config::get('snmpget', 'snmpget'), '-V']);

            $process->run();
            preg_match('/[\w.]+$/', $process->getErrorOutput(), $matches);

            return $matches[0] ?? '';
        });
    }

    /**
     * The OS/distribution and version
     */
    public function os(): string
    {
        return $this->cacheGet('os', function () {
            $info = [];

            // find release file
            if (file_exists('/etc/os-release')) {
                $info = @parse_ini_file('/etc/os-release');
            } else {
                foreach (glob('/etc/*-release') as $file) {
                    $content = file_get_contents($file);
                    // normal os release style
                    $info = @parse_ini_string($content);
                    if (! empty($info)) {
                        break;
                    }

                    // just a string of text
                    if (substr_count($content, PHP_EOL) <= 1) {
                        $info = ['NAME' => trim(str_replace('release ', '', $content))];
                        break;
                    }
                }
            }

            $only = array_intersect_key($info, ['NAME' => true, 'VERSION_ID' => true]);

            return implode(' ', $only);
        });
    }

    /**
     * We want these each runtime, so don't use the global cache
     */
    private function cacheGet(string $name, callable $actual): string
    {
        if (! array_key_exists($name, $this->cache)) {
            $this->cache[$name] = $actual($name);
        }

        return $this->cache[$name];
    }

    private function localCommitData(): array
    {
        return explode('|', $this->cacheGet('local_commit_data', function () {
            $install_dir = Config::get('install_dir');
            $version_process = new Process(['git', 'show', '--quiet', '--pretty=%H|%ct'], $install_dir);
            $version_process->run();

            // failed due to permissions issue
            if ($version_process->getExitCode() == 128 && Str::startsWith($version_process->getErrorOutput(), 'fatal: unsafe repository')) {
                (new Process(['git', 'config', '--global', '--add', 'safe.directory', $install_dir]))->run();
                $version_process->run();
            }

            return rtrim($version_process->getOutput());
        }));
    }
}
