<?php

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -u 100 -l 0 -E -b 1024 ';

$iter         = '1';
$i            = 1;
$rrd_options .= " COMMENT:'                                  % Used\\n'";

foreach (dbFetchRows('SELECT * FROM `router_utilization` where `device_id` = ? ORDER BY `resource`', array($device['device_id'])) as $router_utilization) {
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

    if ($router_utilization['feature']) {
        $label = $router_utilization['resource'] . ' - ' . $router_utilization['feature'];
    } else {
        $label = $router_utilization['resource'];
    }
    $descr        = rrdtool_escape($label, 28);
    $rrd_filename = rrd_name($device['hostname'], array('router_utilization', $router_utilization['id'], $router_utilization['resource']));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_options .= " DEF:used$i=$rrd_filename:used:AVERAGE ";

        $rrd_options .= " AREA:used$i#".$colour.'10';

        $rrd_optionsb .= " LINE1:used$i#".$colour.":'".$descr."' ";
        $rrd_optionsb .= " GPRINT:used$i:LAST:%5.2lf%%\\n";
        $iter++;
        $i++;
    }
}//end foreach

$rrd_options .= $rrd_optionsb;

$rrd_options .= ' HRULE:0#999999';
