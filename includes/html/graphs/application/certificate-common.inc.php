<?php
$name = 'certificate';
$app_id = $app['app_id'];
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['cert_name'])) {
    $cert_name_list=array($vars['cert_name']);
} else {
    $cert_name_list=get_domains_with_certificates($device, $app['app_id']);
}

$int=0;
while (isset($cert_name_list[$int])) {
    $cert_name=$cert_name_list[$int];
    $rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $cert_name));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[]=array(
            'filename' => $rrd_filename,
            'descr'    => $cert_name,
            'ds'       => $rrdVar,
        );
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
