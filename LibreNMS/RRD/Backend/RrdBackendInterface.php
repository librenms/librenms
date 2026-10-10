<?php

/**
 * RrdBackendInterface.php
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
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;

/**
 * Storage and graphing operations for rrd files, using local files or rrdcached when it is set.
 * rrdcached may be on another host, so files are not assumed to be local when it is set.
 *
 * File names are relative to the rrd directory, including those in graph options,
 * which matches how rrdcached resolves them against its base directory.
 *
 * Errors are reported with RrdException subclasses. RrdStoreException subclasses
 * mean the store itself is unusable (connection, permissions), anything else is
 * specific to the file or data.
 *
 * Behavior with rrdcached:
 *  - updates are queued, so data errors (such as a data source count mismatch)
 *    are only detected when the daemon writes the file and are not reported.
 *  - librrd creates the file locally if the daemon can't be reached.
 *
 * Backends release any process or socket they hold when they are destroyed.
 */
interface RrdBackendInterface
{
    /**
     * Create the rrd file described by the definition, an existing file is left untouched.
     *
     * @throws RrdException
     */
    public function create(RrdPath $rrd, RrdDefinition $definition): void;

    /**
     * Store values.
     *
     * @param  array<int|float|string|null>  $values  values in data source order, non-numeric values are stored as unknown
     * @param  int|null  $timestamp  unix time of the values, defaults to now
     *
     * @throws RrdNotFoundException if the file does not exist
     * @throws RrdException
     */
    public function update(RrdPath $rrd, array $values, ?int $timestamp = null): void;

    /**
     * Change the minimum and/or maximum allowed values of data sources in an existing file.
     * Values outside the limits are stored as unknown. A null limit removes it.
     *
     * @param  array<string, array{min?: int|float|null, max?: int|float|null}>  $limits  data source name => limits
     *
     * @throws RrdNotFoundException if the file does not exist
     * @throws RrdException
     */
    public function tune(RrdPath $rrd, array $limits): void;

    /**
     * Check if the rrd file exists
     *
     * @throws RrdException if the store can not be checked
     */
    public function exists(RrdPath $rrd): bool;

    /**
     * Gets the timestamp of the last update to the RRD file
     *
     * @throws RrdException if the store can not be checked
     */
    public function last(RrdPath $rrd): int;

    /**
     * List the rrd files for a host, optionally limited to file names starting with $prefix.
     *
     * @return RrdPath[]
     *
     * @throws RrdException
     */
    public function list(string $hostname, string $prefix = ''): array;

    /**
     * Draw a graph
     *
     * @param  string[]  $options  rrdtool graph options
     * @param  string|null  $timezone  timezone to draw the graph in, defaults to the server timezone
     * @return string the image
     *
     * @throws RrdGraphException
     */
    public function graph(array $options, ?string $timezone = null): string;
}
