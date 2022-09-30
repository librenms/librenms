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

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Traits\RuntimeClassCache;
use Symfony\Component\Process\Process;

class Git
{
    use RuntimeClassCache;

    public function __construct(int $cache = 0)
    {
        $this->runtimeCacheExternalTTL = $cache;
    }

    public static function make(int $cache = 0): Git
    {
        try {
            $git = app()->make('git'); // get the singleton
            $git->runtimeCacheExternalTTL = $cache;

            return $git;
        } catch (BindingResolutionException $e) {
            return new static($cache); // no container, just return a regular instance
        }
    }

    public function isAvailable(): bool
    {
        return $this->cacheGet('isAvailable', function () {
            return $this->repoPresent() && $this->binaryExists();
        });
    }

    public function repoPresent(): bool
    {
        return $this->cacheGet('repoPresent', function () {
            $install_dir = Config::get('install_dir', realpath(__DIR__ . '/../..'));

            return file_exists("$install_dir/.git");
        });
    }

    public function binaryExists(): bool
    {
        return $this->cacheGet('binaryExists', function () {
            exec('git > /dev/null 2>&1', $response, $exit_code);

            return $exit_code === 1;
        });
    }

    public function tag(): string
    {
        return $this->cacheGet('tag', function () {
            if (! $this->isAvailable()) {
                return '';
            }

            return rtrim(shell_exec('git describe --tags 2>/dev/null'));
        });
    }

    public function shortTag(): string
    {
        return $this->cacheGet('shortTag', function () {
            if (! $this->isAvailable()) {
                return '';
            }

            return rtrim(shell_exec('git describe --tags --abbrev=0 2>/dev/null'));
        });
    }

    /**
     * Returns the commit hash of the local HEAD commit
     */
    public function commitHash(): string
    {
        return $this->headCommit()[0] ?? '';
    }

    /**
     * Get the date of the local HEAD commit
     */
    public function commitDate(): string
    {
        return $this->headCommit()[1] ?? '';
    }

    /**
     * Get the current branch
     */
    public function branch(): string
    {
        return $this->cacheGet('branch', function () {
            if (! $this->isAvailable()) {
                return '';
            }

            $branch_process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], Config::get('install_dir'));
            $branch_process->run();

            return rtrim($branch_process->getOutput());
        });
    }

    /**
     * Detect if there are local uncommitted changes.
     */
    public function hasChanges(): bool
    {
        return $this->cacheGet('hasChanges', function () {
            $process = new Process(['git', 'diff-index', '--quiet', 'HEAD']);
            $process->disableOutput();
            $process->run();

            return $process->getExitCode() !== 0;
        });
    }

    /**
     * Note: It assumes origin/master points to github.com/librenms/librenms for this to work.
     */
    public function isOfficialCommit(): bool
    {
        return $this->cacheGet('isOfficialCommit', function () {
            if (! $this->isAvailable()) {
                return false;
            }

            $hash = $this->commitHash();
            $remote = 'origin/master';

            $process = new Process(['git', 'branch', '--remotes', '--contains', $hash, $remote]);
            $process->run();

            if ($process->isSuccessful()) {
                if (trim($process->getOutput()) == $remote) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Get the url of the origin remote
     */
    public function remoteUrl(): string
    {
        return $this->cacheGet('remoteUrl', function () {
            $process = new Process(['git', 'ls-remote', '--get-url', 'origin']);
            $process->run();

            return trim($process->getOutput());
        });
    }

    public function message()
    {
        return $this->cacheGet('remoteUrl', function () {
            $process = new Process(['git', 'ls-remote', '--get-url', 'origin']);
            $process->run();

            return trim($process->getOutput());
        });
    }

    public function log(int $lines = 10)
    {
        return $this->cacheGet('changelog' . $lines, function () {
            return $this->isAvailable()
                ? rtrim(shell_exec('git log -10'))
                : '';
        });
    }

    /**
     * Fetches the remote commit hash from the github api if on the daily release channel
     */
    public function remoteHash(): string
    {
        return $this->remoteCommit()['sha'] ?? '';
    }

    /**
     * Fetches the remote commit from the github api if on the daily release channel
     */
    private function remoteCommit(): array
    {
        return $this->cacheGet('remoteCommit', function () {
            if ($this->isAvailable()) {
                try {
                    return (array) \Http::withOptions(['proxy' => Proxy::forGuzzle()])->get(Config::get('github_api') . 'commits/master')->json();
                } catch (ConnectionException $e) {
                }
            }

            return [];
        });
    }

    private function headCommit(): array
    {
        return $this->cacheGet('headCommit', function () {
            if (! $this->isAvailable()) {
                return [];
            }

            $install_dir = Config::get('install_dir');
            $version_process = new Process(['git', 'show', '--quiet', '--pretty=%H|%ct'], $install_dir);
            $version_process->run();

            // failed due to permissions issue
            if ($version_process->getExitCode() == 128 && Str::startsWith($version_process->getErrorOutput(), 'fatal: unsafe repository')) {
                (new Process(['git', 'config', '--global', '--add', 'safe.directory', $install_dir]))->run();
                $version_process->run();
            }

            return explode('|', rtrim($version_process->getOutput()));
        });
    }
}
