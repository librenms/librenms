<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -u 100 -l 0 -E -b 1024 ';

$iter         = '1';
$i            = 1;
$rrd_options .= " COMMENT:'                           Min   Cur    Max\\n'";

foreach (dbFetchRows('SELECT * FROM `mempools` where `device_id` = ?', array($device['device_id'])) as $mempool) {
    // FIXME generic colour function
    if ($iter == '1') {
        $colour = 'CC0000';
    } elseif ($iter == '2') {
        $colour = '008C00';
    } elseif ($iter == '3') {
        $colour = '4096EE';
    } elseif ($iter == '4') {
        $colour = '73880A';
    } elseif ($iter == '5') {
        $colour = 'D01F3C';
    } elseif ($iter == '6') {
        $colour = '36393D';
    } elseif ($iter == '7') {
        $colour = 'FF0084';
        unset($iter);
    }

    $descr        = rrdtool_escape(short_hrDeviceDescr($mempool['mempool_descr']), 22);
    $rrd_filename = rrd_name($device['hostname'], array('mempool', $mempool['mempool_type'], $mempool['mempool_index']));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_options .= " DEF:mempoolfree$i=$rrd_filename:free:AVERAGE ";
        $rrd_options .= " DEF:mempoolused$i=$rrd_filename:used:AVERAGE ";
        $rrd_options .= " CDEF:mempooltotal$i=mempoolused$i,mempoolused$i,mempoolfree$i,+,/,100,* ";

        $rrd_options .= " AREA:mempooltotal$i#".$colour.'10';

        $rrd_optionsb .= " LINE1:mempooltotal$i#".$colour.":'".$descr."' ";
        $rrd_optionsb .= " GPRINT:mempooltotal$i:MIN:%3.0lf%%";
        $rrd_optionsb .= " GPRINT:mempooltotal$i:LAST:%3.0lf%%";
        $rrd_optionsb .= " GPRINT:mempooltotal$i:MAX:%3.0lf%%\\l ";
        $iter++;
        $i++;
    }
}//end foreach

$rrd_options .= $rrd_optionsb;

$rrd_options .= ' HRULE:0#999999';
