<?php

/**
 * RrdCmd.php
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

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\TimeSeriesPoint;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use Log;

class PhpRrd implements RrdBackendInterface
{
    private readonly RrdtoolRrd $rrdtool;
    private readonly string $rrdcached;

    public function __construct()
    {
        // Create a RrdtoolRrd to fall through to
        $this->rrdtool = new RrdtoolRrd();
        $this->rrdcached = LibrenmsConfig::get('rrdcached', '');

        putenv('LC_ALL=C'); // force english/standard output
        if ($this->rrdcached) {
            putenv('RRDCACHED_ADDRESS=' . $this->rrdcached);
        }
        if (session('preferences.timezone')) {
            putenv('TZ=' . session('preferences.timezone'));
        }
    }

    /**
     * Close rrdtool process.
     * This should be done before exiting
     */
    public function terminate(): void
    {
        // Clean up the RrdCmd
        $this->rrdtool->terminate();
    }

    /**
     * @throws RrdException
     */
    public function lastUpdate(string $filename): ?TimeSeriesPoint
    {
        if ($this->rrdcached) {
            // PHP-RRD does not support this command with cached
            return $this->rrdtool->lastUpdate($filename);
        }

        Log::debug("PHPRRD[%glastupdate $filename%n]", ['color' => true]);
        $lastUpdate = rrd_lastupdate($filename);
        if (! $lastUpdate) {
            return null;
        }

        return new TimeSeriesPoint($lastUpdate['last_update'], array_combine($lastUpdate['ds_navm'], $lastUpdate['data']));
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     *
     * @internal
     */
    public function create(string $filename, array $data): void
    {
        Log::debug('PHPRRD[%gcreate ' . implode(' ', $data) . '%n]', ['color' => true]);
        if (! rrd_create($filename, $data)) {
            Log::warning('Error creating RRD file: ' . rrd_error());
        }
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     */
    public function update(string $filename, array $data): void
    {
        $data = 'N:' . implode(':', array_map(fn ($v) => is_numeric($v) ? $v : 'U', $data));
        Log::debug("PHPRRD[%gupdate $filename $data%n]", ['color' => true]);

        // The \RRDUpdater class does not use rrdcached, so we need to use the function
        if (! rrd_update($filename, [$data])) {
            throw RrdException::parse(rrd_error());
        }
    }

    /**
     * @param  string[]  $options
     */
    public function tune(string $filename, array $options): bool
    {
        if ($this->rrdcached) {
            // PHP-RRD does not support this command with cached
            return $this->rrdtool->tune($filename, $options);
        }

        Log::debug("PHPRRD[%gtune $filename " . implode('|', $options) . '%n]', ['color' => true]);
        if (! rrd_tune($filename, $options)) {
            Log::warning('Error tuning RRD file: ' . rrd_error());

            return false;
        }

        return true;
    }

    public function last(string $filename): string
    {
        if ($this->rrdcached) {
            // PHP-RRD does not support this command with cached
            return $this->rrdtool->last($filename);
        }

        Log::debug("PHPRRD[%glast $filename%n]", ['color' => true]);
        $last = rrd_last($filename);
        if (! $last) {
            return "$filename: No such file or directory";
        }

        return (string) $last;
    }

    /**
     * @param  string|string[]  $prefix
     * @return string[]
     */
    public function list(string $dir, string|array $prefix): array
    {
        // Not implemented in PHP-RRD
        return $this->rrdtool->list($dir, $prefix);
    }

    /**
     * @param  string[]  $options
     */
    public function graph(array $options): string
    {
        Log::debug('PHPRRD[%graph ' . implode(' ', $options) . '%n]', ['color' => true]);
        $rrd = new \RRDGraph('-');
        $rrd->setOptions($options);
        try {
            $data = $rrd->saveVerbose();
        } catch (\Exception $e) {
            throw new RrdGraphException($e->getMessage());
        }

        return $data['image'];
    }
}
