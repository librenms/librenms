<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/graphs/common.inc.php';

$iter         = '1';
$rrd_options .= " COMMENT:'                    Size      Used    %age\\l'";

foreach (dbFetchRows('SELECT * FROM storage where device_id = ?', array($device['device_id'])) as $storage) {
    // FIXME generic colour function
    if ($iter == '1') {
        $colour = 'CC0000';
    }
    else if ($iter == '2') {
        $colour = '008C00';
    }
    else if ($iter == '3') {
        $colour = '4096EE';
    }
    else if ($iter == '4') {
        $colour = '73880A';
    }
    else if ($iter == '5') {
        $colour = 'D01F3C';
    }
    else if ($iter == '6') {
        $colour = '36393D';
    }
    else if ($iter == '7') {
        $colour = 'FF0084';
        $iter   = '0';
    }

    $descr        = rrdtool_escape($storage['storage_descr'], 12);
    $rrd          = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('storage-'.$storage['storage_mib'].'-'.$storage['storage_descr'].'.rrd');
    $rrd_options .= " DEF:$storage[storage_id]used=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:$storage[storage_id]free=$rrd:free:AVERAGE";
    $rrd_options .= " CDEF:$storage[storage_id]size=$storage[storage_id]used,$storage[storage_id]free,+";
    $rrd_options .= " CDEF:$storage[storage_id]perc=$storage[storage_id]used,$storage[storage_id]size,/,100,*";
    $rrd_options .= " LINE1.25:$storage[storage_id]perc#".$colour.":'$descr'";
    $rrd_options .= " GPRINT:$storage[storage_id]size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:$storage[storage_id]used:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:$storage[storage_id]perc:LAST:%5.2lf%%\\l";
    $iter++;
}//end foreach
