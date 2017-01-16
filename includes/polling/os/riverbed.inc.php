<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2017 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.1.0', '-OQv'), '"');
$serial   = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.2.0', '-OQv'), '"');
$version  = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.3.0', '-OQv'), '"');


/* optimisation oids
 *
 * half-open   .1.3.6.1.4.1.17163.1.1.5.2.3.0
 * half-closed .1.3.6.1.4.1.17163.1.1.5.2.4.0
 * establised  .1.3.6.1.4.1.17163.1.1.5.2.5.0
 * active      .1.3.6.1.4.1.17163.1.1.5.2.6.0
 * total       .1.3.6.1.4.1.17163.1.1.5.2.7.0
 *
 */

$conn_half_open   = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.3.0', '-OUQn');
$conn_half_closed = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.4.0', '-OUQn');
$conn_established = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.5.0', '-OUQn');
$conn_active      = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.6.0', '-OUQn');
$conn_total       = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.7.0', '-OUQn');

if ($conn_half_open >= 0 && $conn_half_closed >= 0 && $conn_established >= 0 && $conn_active >= 0 && $conn_total >= 0) {
    $rrd_def = array(
        'DS:half_open:GAUGE:600:0:U',
        'DS:half_closed:GAUGE:600:0:U',
        'DS:established:GAUGE:600:0:U',
        'DS:active:GAUGE:600:0:U',
        'DS:total:GAUGE:600:0:U',
    );

    $fields = array(
        'half_open'   => $conn_half_open,
        'half_closed' => $conn_half_closed,
        'established' => $conn_established,
        'active'      => $conn_established,
        'total'       => $conn_total,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_connections', $tags, $fields);
    $graphs['riverbed_connections'] = true;
}

/* datastore oids
 *
 * hits .1.3.6.1.4.1.17163.1.1.5.4.1.0
 * miss .1.3.6.1.4.1.17163.1.1.5.4.2.0
 *
 */

$datastore_hits = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.4.1.0', '-OUQn');
$datastore_miss = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.4.2.0', '-OUQn');

if ($datastore_hits >= 0 && $datastore_miss >= 0) {
    $rrd_def = array(
        'DS:datastore_hits:GAUGE:600:0:U',
        'DS:datastore_miss:GAUGE:600:0:U',
    );

    $fields = array(
        'datastore_hits' => $datastore_hits,
        'datastore_miss' => $datastore_miss,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_datastore', $tags, $fields);
    $graphs['riverbed_datastore'] = true;
}

/* optimization oids
 *
 * optimized   .1.3.6.1.4.1.17163.1.1.5.2.1.0
 * passthrough .1.3.6.1.4.1.17163.1.1.5.2.2.0
 *
 */

$conn_optimized   = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.1.0', '-OUQn');
$conn_passthrough = snmp_get($device, '.1.3.6.1.4.1.17163.1.1.5.2.2.0', '-OUQn');

if ($conn_optimized >= 0 && $conn_passthrough >= 0) {
    $rrd_def = array(
        'DS:conn_optimized:GAUGE:600:0:U',
        'DS:conn_passthrough:GAUGE:600:0:U',
    );

    $fields = array(
        'conn_optimized' => $conn_optimized,
        'conn_passthrough' => $conn_passthrough,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_optimisation', $tags, $fields);
    $graphs['riverbed_optimisation'] = true;
}
