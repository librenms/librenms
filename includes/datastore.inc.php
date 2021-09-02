<?php
/*
 * LibreNMS abstract data storage interface to both rrdtool & influxdb
 *
 * Copyright (c) 2016 Paul D. Gear <paul@librenms.org>
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
 */

/**
 * Datastore-independent function which should be used for all polled metrics.
 *
 * RRD Tags:
 *   rrd_def     RrdDefinition
 *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
 *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
 *   rrd_step             int: rrd step, defaults to 300
 *
 * @param array $device
 * @param string $measurement Name of this measurement
 * @param array $tags tags for the data (or to control rrdtool)
 * @param array|mixed $fields The data to update in an associative array, the order must be consistent with rrd_def,
 *                            single values are allowed and will be paired with $measurement
 */
function data_update($device, $measurement, $tags, $fields)
{
    $datastore = app('Datastore');
    $datastore->put($device, $measurement, $tags, $fields);
}
