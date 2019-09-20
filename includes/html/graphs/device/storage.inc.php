<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/common.inc.php';

$iter         = '1';
$rrd_options .= " COMMENT:'                        Size      Used    % Used\\l'";

foreach (dbFetchRows('SELECT * FROM storage where device_id = ?', array($device['device_id'])) as $storage) {
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
        $iter   = '0';
    }

    $descr        = rrdtool_escape($storage['storage_descr'], 16);
    $rrd          = rrd_name($device['hostname'], array('storage', $storage['storage_mib'], $storage['storage_descr']));
    $rrd_options .= " DEF:{$storage['storage_id']}used=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:{$storage['storage_id']}free=$rrd:free:AVERAGE";
    $rrd_options .= " CDEF:{$storage['storage_id']}size={$storage['storage_id']}used,{$storage['storage_id']}free,+";
    $rrd_options .= " CDEF:{$storage['storage_id']}perc={$storage['storage_id']}used,{$storage['storage_id']}size,/,100,*";
    $rrd_options .= " LINE1.25:{$storage['storage_id']}perc#".$colour.":'$descr'";
    $rrd_options .= " GPRINT:{$storage['storage_id']}size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:{$storage['storage_id']}used:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:{$storage['storage_id']}perc:LAST:%5.2lf%%\\l";
    $iter++;
}//end foreach
