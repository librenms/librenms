<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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

/*
 * Bind9 Query Graph
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Graphs
 */

$unitlen = 10;
$bigdescrlen = 9;
$smalldescrlen = 9;
$dostack = 0;
$printtotal = 0;
$unit_text = 'query/sec';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id']]);
$array = [
    'any',
    'a',
    'aaaa',
    'cname',
    'mx',
    'ns',
    'ptr',
    'soa',
    'srv',
    'spf',
];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => strtoupper($ds),
            'ds' => $ds,
        ];
    }
} else {
    echo "file missing: $file";
}

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'incoming']);
$array = [
    'afsdb',
    'apl',
    'caa',
    'cdnskey',
    'cds',
    'cert',
    'dhcid',
    'dlv',
    'dnskey',
    'ds',
    'ipseckey',
    'key',
    'kx',
    'loc',
    'naptr',
    'nsec',
    'nsec3',
    'nsec3param',
    'rrsig',
    'rp',
    'sig',
    'sshfp',
    'ta',
    'tkey',
    'tlsa',
    'tsig',
    'txt',
    'uri',
    'dname',
    'axfr',
    'ixfr',
    'opt',
];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => strtoupper($ds),
            'ds' => $ds,
        ];
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
