<?php

require 'includes/html/graphs/common.inc.php';
$i = 0;
$scale_min = 0;
$nototal = 1;
$unit_text = 'Per Sec.';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'freeradius-proxy_auth', $app['app_id']]);
$fr_proxy_auth_array = [
    'responses' => 'Responses',
    'duplicate_requests' => 'Duplicate Requests',
    'malformed_requests' => 'Malformed Requests',
    'invalid_requests' => 'Invalid Requests',
    'dropped_requests' => 'Dropped Requests',
    'unknown_types' => 'Unknown Types',
];
$colours = 'mixed';
$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($fr_proxy_auth_array as $ds => $descr) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $descr;
        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}
require 'includes/html/graphs/generic_multi_line.inc.php';
