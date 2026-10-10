<?php

/**
 * Rrdtool.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\RRD\Backend;

use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdFileExistsException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Util\Debug;
use Log;

/**
 * Pipes commands to an rrdtool process, rrdtool forwards them to rrdcached when it is configured.
 */
class Rrdtool implements RrdBackendInterface
{
    /**
     * @param  string|null  $rrdcached  rrdcached address the rrdtool process forwards commands to
     * @param  RrdProcess|null  $process  the rrdtool process, started on first use when not given
     */
    public function __construct(
        protected readonly ?string $rrdcached,
        private ?RrdProcess $process = null,
    ) {
    }

    /**
     * @throws RrdException
     */
    public function create(RrdPath $rrd, RrdDefinition $definition): void
    {
        try {
            $this->run('create', $rrd->defaultPath(), [...$definition->getCreateArguments(), '-O']);
        } catch (RrdFileExistsException) {
            Log::debug("RRD[%g$rrd already exists%n]", ['color' => true]);
        }
    }

    /**
     * @param  array<int|float|string|null>  $values
     *
     * @throws RrdException
     */
    public function update(RrdPath $rrd, array $values, ?int $timestamp = null): void
    {
        $this->run('update', $rrd->defaultPath(), [($timestamp ?? 'N') . ':' . $this->formatValues($values)]);
    }

    /**
     * @param  array<string, array{min?: int|float|null, max?: int|float|null}>  $limits
     *
     * @throws RrdException
     */
    public function tune(RrdPath $rrd, array $limits): void
    {
        $this->run('tune', $rrd->defaultPath(), $this->limitArguments($limits));
    }

    /**
     * @throws RrdException
     */
    public function last(RrdPath $rrd): int
    {
        return (int) $this->run('last', $rrd->defaultPath());
    }

    /**
     * @return RrdPath[]
     *
     * @throws RrdException
     */
    public function list(string $hostname, string $prefix = ''): array
    {
        if (! RrdPath::remoteCachedEnabled()) {
            return $this->listLocal($hostname, $prefix);
        }

        try {
            $output = $this->run('list', '/' . RrdPath::make($hostname)->relativePath());
        } catch (RrdNotFoundException) {
            return [];
        }

        return $this->toPaths($hostname, $prefix, explode("\n", $output));
    }

    /**
     * Graphs run in their own short lived rrdtool process so they can use the viewer's timezone
     *
     * @param  string[]  $options
     */
    public function graph(array $options, ?string $timezone = null): string
    {
        try {
            $process = app(RrdProcess::class, ['timeout' => 300, 'timezone' => $timezone]);

            return $process->run('"' . implode('" "', ['graph', '-', ...$options]) . '"');
        } catch (RrdException $e) {
            throw new RrdGraphException($e->getMessage(), 'Error');
        }
    }

    /**
     * Pipe a command to rrdtool
     *
     * @param  string[]  $arguments
     *
     * @throws RrdException
     */
    private function run(string $command, string $filename, array $arguments = []): string
    {
        $this->process ??= app(RrdProcess::class, ['timeout' => 1200]);
        $output = $this->process->run(implode(' ', [$command, $filename, ...$arguments]));

        if (Debug::isVerbose() && $output) {
            Log::debug('RRDtool Output: ' . $output);
        }

        return $output;
    }

    /**
     * @param  array<int|float|string|null>  $values
     */
    protected function formatValues(array $values): string
    {
        return implode(':', array_map(fn ($v) => is_numeric($v) ? $v : 'U', $values));
    }

    /**
     * @param  array<string, array{min?: int|float|null, max?: int|float|null}>  $limits
     * @return string[] rrdtool tune arguments
     */
    protected function limitArguments(array $limits): array
    {
        $arguments = [];
        foreach ($limits as $ds => $limit) {
            if (array_key_exists('min', $limit)) {
                array_push($arguments, '--minimum', $ds . ':' . ($limit['min'] ?? 'U'));
            }
            if (array_key_exists('max', $limit)) {
                array_push($arguments, '--maximum', $ds . ':' . ($limit['max'] ?? 'U'));
            }
        }

        return $arguments;
    }

    /**
     * Filter file names by prefix and turn them into paths for the host
     *
     * @param  string[]  $files
     * @return RrdPath[]
     */
    protected function toPaths(string $hostname, string $prefix, array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $file = basename(trim($file));
            if ($file !== '' && str_starts_with($file, $prefix) && str_ends_with($file, '.rrd')) {
                $paths[] = RrdPath::make($hostname, $file);
            }
        }

        return $paths;
    }

    /**
     * @return RrdPath[]
     */
    protected function listLocal(string $hostname, string $prefix): array
    {
        return $this->toPaths($hostname, $prefix, glob(RrdPath::make($hostname)->fullPath() . DIRECTORY_SEPARATOR . $prefix . '*.rrd') ?: []);
    }
}
