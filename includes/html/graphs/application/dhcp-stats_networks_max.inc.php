<?php
$unit_text     = 'Max';
$unitlen       = 20;
$bigdescrlen   = 20;
$smalldescrlen = 20;
$category      = 'networks';

$rrdVar        = 'max';

$name          = 'dhcp-stats';
$app_id        = $app['app_id'];
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$arrays = get_arrays_with_application($device, $app_id, $name, $category);

$int=0;
while (isset($arrays[$int])) {
    $array = $arrays[$int];
    $rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $array));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        list($net, $subnet) = explode('_', str_replace($category.'-', '', $array));
        $rrd_list[] = array(
            'filename' => $rrd_filename,
            'descr'    => $net.'/'.$subnet,
            'ds'       => $rrdVar,
        );
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
