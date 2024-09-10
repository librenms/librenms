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

    /** @var string */
    private $install_dir;

    public function __construct(int $cache = 0)
    {
        $this->runtimeCacheExternalTTL = $cache;
        $this->install_dir = Config::get('install_dir', realpath(__DIR__ . '/../..'));
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
            return file_exists("$this->install_dir/.git");
        });
    }

    public function binaryExists(): bool
    {
        return $this->cacheGet('binaryExists', function () {
            return $this->run('help', [])->isSuccessful();
        });
    }

    public function tag(): string
    {
        return $this->cacheGet('tag', function () {
            return $this->isAvailable()
                ? rtrim($this->run('describe', ['--tags'])->getOutput())
                : '';
        });
    }

    public function shortTag(): string
    {
        return $this->cacheGet('shortTag', function () {
            return $this->isAvailable()
                ? rtrim($this->run('describe', ['--tags', '--abbrev=0'])->getOutput())
                : '';
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
            return $this->isAvailable()
                ? rtrim($this->run('rev-parse', ['--abbrev-ref', 'HEAD'])->getOutput())
                : '';
        });
    }

    /**
     * Detect if there are local uncommitted changes.
     */
    public function hasChanges(): bool
    {
        return $this->cacheGet('hasChanges', function () {
            return $this->isAvailable() && ! $this->run('diff-index', ['--quiet', 'HEAD'])->isSuccessful();
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

            $process = $this->run('branch', ['--remotes', '--contains', $this->commitHash(), 'origin/master']);

            return $process->isSuccessful() && trim($process->getOutput()) == 'origin/master';
        });
    }

    /**
     * Get the url of the origin remote
     */
    public function remoteUrl(): string
    {
        return $this->cacheGet('remoteUrl', function () {
            return $this->isAvailable()
                ? rtrim($this->run('ls-remote', ['--get-url', 'origin'])->getOutput())
                : '';
        });
    }

    public function message(): string
    {
        return $this->cacheGet('remoteUrl', function () {
            return $this->isAvailable()
                ? rtrim($this->run('log', ['--pretty=format:%s', '-n', '1'])->getOutput())
                : '';
        });
    }

    public function log(int $lines = 10): string
    {
        return $this->cacheGet('changelog' . $lines, function () use ($lines) {
            return $this->isAvailable()
                ? rtrim($this->run('log', ['-' . $lines])->getOutput())
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
                    return (array) Http::client()->get(Config::get('github_api') . 'commits/master')->json();
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

            $version_process = $this->run('show', ['--quiet', '--pretty=%H|%ct']);

            // failed due to permissions issue
            if ($version_process->getExitCode() == 128 && Str::startsWith($version_process->getErrorOutput(), 'fatal: unsafe repository')) {
                $this->run('config', ['--global', '--add', 'safe.directory', $this->install_dir]); // try to fix
                $version_process = $this->run('show', ['--quiet', '--pretty=%H|%ct']); // and try again
            }

            return explode('|', rtrim($version_process->getOutput()));
        });
    }

    private function run(string $command, array $options): Process
    {
        $version_process = new Process(array_merge(['git', $command], $options), $this->install_dir);
        $version_process->run();

        return $version_process;
    }
}
