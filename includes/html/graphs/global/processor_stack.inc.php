<?php

$i = 0;

foreach (dbFetchRows('SELECT * FROM `processors` AS P, devices AS D WHERE D.device_id = P.device_id') as $proc) {
    $rrd_filename = rrd_name($proc['hostname'], array('processor', $proc['processor_type'], $proc['processor_index']));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $descr = short_hrDeviceDescr($proc['processor_descr']);

        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $descr;
        $rrd_list[$i]['ds']       = 'usage';
        $i++;
    }
}

$unit_text = 'Load %';

$units       = '%';
$total_units = '%';
$colours     = 'oranges';

$scale_min = '0';
$scale_max = '100';

$divider   = $i;
$text_orig = 1;
$nototal   = 1;

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
