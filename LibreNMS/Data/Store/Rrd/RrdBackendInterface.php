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

namespace LibreNMS\Data\Store\Rrd;

use LibreNMS\Data\Store\TimeSeriesPoint;
use LibreNMS\Exceptions\RrdException;

interface RrdBackendInterface
{
    /**
     * Clean up
     * This should be done before exiting
     */
    public function terminate(): void;

    /**
     * Returns the data from the last update
     */
    public function lastUpdate(string $filename): ?TimeSeriesPoint;

    /**
     * Create a rrd database at $filename using the supplied arguments
     *
     * @param  string[]  $def
     *
     * @throws RrdException
     */
    public function create(string $filename, array $def): void;

    /**
     * Updates an rrd database at $filename using the supplied data
     *
     * @param  string[]  $data
     *
     * @throws RrdException
     */
    public function update(string $filename, array $data): void;

    /**
     * Modify an rrd file's max value
     *
     * @param  string[]  $options
     */
    public function tune(string $filename, array $options): bool;

    /**
     * Return the last timestamp a RRD file was updated or an error message if it does not
     */
    public function last(string $filename): string;

    /**
     * Return a list of files
     *
     * @param  string|string[]  $prefix
     * @return string[]
     */
    public function list(string $dir, string|array $prefix): array;

    /**
     * Returns a graph using $options
     *
     * @param  string[]  $options
     */
    public function graph(array $options): string;
}
