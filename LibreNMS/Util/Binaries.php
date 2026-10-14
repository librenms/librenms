<?php

/**
 * Binaries.php
 *
 * Location and version of external programs on this node.
 * Each node may be installed differently, so these are detected and cached locally,
 * never stored in the global config.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\Process\Process;

class Binaries
{
    private const CACHE_KEY = 'librenms-binaries';
    /** paths that were set in the global config, imported by each node when it has no path cached */
    public const LEGACY_CACHE_KEY = 'librenms-legacy-binary-paths';
    /** searched after PATH, which may be empty when running from cron or systemd */
    private const SEARCH_DIRS = ['/usr/local/sbin', '/usr/local/bin', '/usr/sbin', '/usr/bin', '/sbin', '/bin'];

    /** @var array<string, string|null> */
    private array $paths = [];
    /** @var array<string, string> */
    private array $versions = [];
    /** @var array{paths?: array<string, string>, versions?: array<string, array{signature: string, version: string}>}|null */
    private ?array $cached = null;
    /** @var array<string, string>|null */
    private ?array $legacy = null;

    /**
     * @param  Repository  $cache  must be local to this node
     * @param  Repository  $shared  shared by all nodes
     */
    public function __construct(
        private readonly Repository $cache,
        private readonly Repository $shared,
    ) {
    }

    /**
     * Full path to the binary, or just the name if it was not found
     */
    public function path(string $binary): string
    {
        return $this->find($binary) ?? $binary;
    }

    /**
     * Full path to the binary, or null if it was not found
     */
    public function find(string $binary): ?string
    {
        if (array_key_exists($binary, $this->paths)) {
            return $this->paths[$binary];
        }

        // the cached path is only a hint, the binary may have been moved or removed since
        $cached = $this->cache()['paths'][$binary] ?? null;
        if (is_string($cached) && self::isExecutableFile($cached)) {
            return $this->paths[$binary] = $cached;
        }

        $path = $this->legacyPath($binary) ?? self::locate($binary);
        if ($path !== null) {
            $this->store('paths', $binary, $path);
        }

        return $this->paths[$binary] = $path;
    }

    public function rrdtoolVersion(): string
    {
        return $this->version('rrdtool', ['--version'], function (Process $process): string {
            preg_match('/^RRDtool ([\w.]+) /', $process->getOutput(), $matches);

            return str_replace('1.7.01.7.0', '1.7.0', $matches[1] ?? '');
        });
    }

    public function netSnmpVersion(): string
    {
        return $this->version('snmpget', ['-V'], function (Process $process): string {
            preg_match('/[\w.]+$/', $process->getErrorOutput(), $matches);

            return $matches[0] ?? '';
        });
    }

    /**
     * Use this path for the rest of this process, it is not cached. For testing.
     */
    public function setPath(string $binary, ?string $path): void
    {
        $this->paths[$binary] = $path;
    }

    /**
     * Use this version for the rest of this process, it is not cached. For testing.
     */
    public function setVersion(string $binary, string $version): void
    {
        $this->versions[$binary] = $version;
    }

    /**
     * @param  string[]  $args
     * @param  callable(Process): string  $parse
     */
    private function version(string $binary, array $args, callable $parse): string
    {
        if (isset($this->versions[$binary])) {
            return $this->versions[$binary];
        }

        $path = $this->find($binary);
        if ($path === null) {
            return $this->versions[$binary] = '';
        }

        // an upgrade replaces the binary, which changes the signature
        $signature = self::signature($path);
        $cached = $this->cache()['versions'][$binary] ?? null;
        if (is_array($cached) && $cached['signature'] === $signature) {
            return $this->versions[$binary] = $cached['version'];
        }

        $process = new Process([$path, ...$args]);
        $process->run();
        $version = $parse($process);

        if ($version !== '') {
            $this->store('versions', $binary, ['signature' => $signature, 'version' => $version]);
        }

        return $this->versions[$binary] = $version;
    }

    /**
     * The path that was set in the global config, if it exists on this node
     */
    private function legacyPath(string $binary): ?string
    {
        if ($this->legacy === null) {
            try {
                $legacy = $this->shared->get(self::LEGACY_CACHE_KEY);
            } catch (Exception) {
                $legacy = null; // no shared cache yet (not installed)
            }
            $this->legacy = is_array($legacy) ? $legacy : [];
        }

        $path = $this->legacy[$binary] ?? null;

        return is_string($path) && str_starts_with($path, '/') && self::isExecutableFile($path) ? $path : null;
    }

    private static function locate(string $binary): ?string
    {
        if (str_contains($binary, '/')) {
            return self::isExecutableFile($binary) ? $binary : null;
        }

        $path_dirs = explode(PATH_SEPARATOR, (string) getenv('PATH'));
        foreach (array_unique([...$path_dirs, ...self::SEARCH_DIRS]) as $dir) {
            // skip relative entries such as an empty PATH, which would search the working directory
            if (str_starts_with($dir, '/') && self::isExecutableFile($file = rtrim($dir, '/') . '/' . $binary)) {
                return $file;
            }
        }

        return null;
    }

    private static function isExecutableFile(string $path): bool
    {
        return @is_file($path) && @is_executable($path);
    }

    private static function signature(string $path): string
    {
        $stat = @stat($path);

        return $stat ? "$path:{$stat['ino']}:{$stat['size']}:{$stat['mtime']}" : $path;
    }

    /**
     * @return array{paths?: array<string, string>, versions?: array<string, array{signature: string, version: string}>}
     */
    private function cache(): array
    {
        if ($this->cached === null) {
            try {
                $cached = $this->cache->get(self::CACHE_KEY);
            } catch (Exception) {
                $cached = null;
            }
            $this->cached = is_array($cached) ? $cached : [];
        }

        return $this->cached;
    }

    /**
     * @param  'paths'|'versions'  $type
     * @param  string|array{signature: string, version: string}  $value
     */
    private function store(string $type, string $key, string|array $value): void
    {
        try {
            // another process may have updated the cache since it was read
            $cached = $this->cache->get(self::CACHE_KEY);
            $cached = is_array($cached) ? $cached : [];
            $cached[$type][$key] = $value;
            $this->cache->forever(self::CACHE_KEY, $cached);
            $this->cached = $cached;
        } catch (Exception) {
            // not cached, it will be detected again next time
        }
    }
}
