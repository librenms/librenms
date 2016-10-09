<?php

$i = 1;

foreach (dbFetchRows('SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE D.device_id = ? AND U.device_id = D.device_id', array($device['device_id'])) as $disk) {
    $rrd_filename = rrd_name($disk['hostname'], array('ucd_diskio', $disk['diskio_descr']));
    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $disk['diskio_descr'];
        $rrd_list[$i]['ds_in']    = $ds_in;
        $rrd_list[$i]['ds_out']   = $ds_out;
        $i++;
    }
}
