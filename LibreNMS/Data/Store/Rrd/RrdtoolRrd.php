<?php

/**
 * RrdtoolRrd.php
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

namespace LibreNMS\Data\Store\Rrd;

use LibreNMS\Data\Store\Rrd;
use LibreNMS\Data\Store\TimeSeriesPoint;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Util\Debug;
use Log;

class RrdtoolRrd implements RrdBackendInterface
{
    private ?RrdProcess $rrd = null;

    public function __construct()
    {
    }

    private function init(int $timeout = 600): void
    {
        $this->rrd ??= app(RrdProcess::class, ['timeout' => $timeout]);
    }

    /**
     * Close rrdtool process.
     * This should be done before exiting
     */
    public function terminate(): void
    {
        $this->rrd?->stop();
    }

    /**
     * @throws RrdException
     */
    public function lastUpdate(string $filename): ?TimeSeriesPoint
    {
        $output = $this->command('lastupdate', $filename);

        if (preg_match('/((?: \w+)+)\n\n(\d+):((?: [\d.-]+)+)\nOK/', $output, $matches)) {
            $data = array_combine(
                explode(' ', ltrim($matches[1])),
                explode(' ', ltrim($matches[3])),
            );

            return new TimeSeriesPoint((int) $matches[2], $data);
        }

        return null;
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     */
    public function create(string $filename, array $data): void
    {
        $this->command('create', $filename, $data);
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     */
    public function update(string $filename, array $data): void
    {
        $data = 'N:' . implode(':', array_map(fn ($v) => is_numeric($v) ? $v : 'U', $data));

        $this->command('update', $filename, [$data]);
    }

    /**
     * Modify an rrd file's max value and trim the peaks as defined by rrdtool
     *
     * @param  string[]  $options
     */
    public function tune(string $filename, array $options): bool
    {
        try {
            $this->command('tune', $filename, $options);
        } catch (RrdException $e) {
            if (! $e instanceof RrdNotFoundException) {
                Log::debug('RRD tune failed: ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Generates and pipes a command to rrdtool
     *
     * @param  string[]  $options  rrdtool command options
     *
     * @throws RrdException thrown when the rrdtool process(s) cannot be started
     */
    private function command(string $command, string $filename, array $options = []): string
    {
        $cmd = Rrd::buildCommand($command, $filename, $options);
        $commandLine = implode(' ', $cmd);

        $this->init();
        $output = $this->rrd->run($commandLine);

        if (Debug::isVerbose() && $output) {
            Log::debug('RRDtool Output: ' . $output);
        }

        return $output;
    }

    public function last(string $filename): string
    {
        return $this->command('last', $filename);
    }

    /**
     * @param  string|string[]  $prefix
     * @return string[]
     */
    public function list(string $dir, string|array $prefix): array
    {
        $output = $this->command('list', $dir);

        return array_filter(explode("\n", trim($output)), fn ($file) => str_starts_with((string) $file, $prefix));
    }

    /**
     * @param  string[]  $options
     */
    public function graph(array $options): string
    {
        try {
            $command = Rrd::buildCommand('graph', '-', $options);

            $this->init(300);
            $image = $this->rrd->run('"' . implode('" "', $command) . '"');
            $this->rrd->stop();

            return $image;
        } catch (RrdException $e) {
            throw new RrdGraphException($e->getMessage(), 'Error');
        }
    }
}
