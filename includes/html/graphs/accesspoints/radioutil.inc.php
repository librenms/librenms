<?php

$rrd_filename = Rrd::name($device['hostname'], ['arubaap', $ap['name'] . $ap['radio_number']]);

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['descr'] = 'radioutil';
$rrd_list[0]['ds'] = 'radioutil';

$unit_text = 'Percent';

$units = '';
$total_units = '';
$colours = 'mixed';

$scale_min = '0';

$nototal = 1;

if ($rrd_list) {
    include 'includes/html/graphs/generic_multi_line.inc.php';
}
