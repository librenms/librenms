<?php

require 'includes/html/graphs/common.inc.php';
$name = 'dhcp-stats';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Leases';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

$array = [
    'dhcp_total' => ['descr' => 'Total', 'colour' => '582A72'],
    'dhcp_active' => ['descr' => 'Active', 'colour' => '28774F'],
    'dhcp_expired' => ['descr' => 'Expired', 'colour' => 'AA6C39'],
    'dhcp_released' => ['descr' => 'Released', 'colour' => '88CC88'],
    'dhcp_abandoned' => ['descr' => 'Abandoned', 'colour' => 'D46A6A'],
    'dhcp_reset' => ['descr' => 'Reset', 'colour' => 'FFD1AA'],
    'dhcp_bootp' => ['descr' => 'BootP', 'colour' => '582A72'],
    'dhcp_backup' => ['descr' => 'Backup', 'colour' => 'AA5439'],
    'dhcp_free' => ['descr' => 'Free', 'colour' => '28536C'],
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
