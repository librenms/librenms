<?php

use App\Models\RadiusServer;

// RADIUS response time per server
$rrd_list = [];
foreach (RadiusServer::where('device_id', $device['device_id'])->get() as $server) {
    $rrd_filename = Rrd::name($device['hostname'], ['radius', "$server->address-$server->port"]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $server->address,
            'ds' => 'rtt',
        ];
    }
}

$unit_text = 'Milliseconds';
$units = 'ms';
$total_units = 'ms';
$colours = 'mixed';
$scale_min = '0';
$nototal = 1;
$descr_len = 16; // room for an IPv4 address

if ($rrd_list) {
    include 'includes/html/graphs/generic_multi_line.inc.php';
}
