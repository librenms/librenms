<?php

$unitlen = 10;
$bigdescrlen = 9;
$smalldescrlen = 9;
$dostack = 0;
$printtotal = 0;
$unit_text = 'RR sets';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'rrnegative']);
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
    'nxdomain',
    'axfr',
    'ixfr',
    'opt',
];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => '!' . strtoupper($ds),
            'ds' => $ds,
        ];
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
