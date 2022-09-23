<?php

// This is my translation of Smokeping's graphing.
// Thanks to Bill Fenner for Perl->Human translation:>
use LibreNMS\Config;

$scale_min = 0;
$scale_rigid = true;

require 'includes/html/graphs/common.inc.php';
require 'includes/html/graphs/device/smokeping_common.inc.php';

$i = 0;
$pings = Config::get('smokeping.pings');
$iter = 0;
$colourset = 'mixed';

if ($width > '500') {
    $descr_len = 18;
} else {
    $descr_len = (12 + round(($width - 275) / 8));
}

if ($width > '500') {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . " RTT      Loss    SDev   RTT\:SDev\l'";
} else {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . " RTT      Loss    SDev   RTT\:SDev\l'";
}

foreach ($smokeping_files[$direction][$device['hostname']] as $source => $filename) {
    if (! Config::has("graph_colours.$colourset.$iter")) {
        $iter = 0;
    }

    $colour = Config::get("graph_colours.$colourset.$iter");
    $iter++;

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($source, $descr_len);

    $filename = generate_smokeping_file($device, $filename);
    $rrd_options .= " DEF:median$i=" . $filename . ':median:AVERAGE ';
    $rrd_options .= " DEF:loss$i=" . $filename . ':loss:AVERAGE';
    $rrd_options .= " CDEF:ploss$i=loss$i,$pings,/,100,*";
    $rrd_options .= " CDEF:dm$i=median$i";
    // $rrd_options .= " CDEF:dm$i=median$i,0,".$max->{$start}.",LIMIT";
    // start emulate Smokeping::calc_stddev
    foreach (range(1, $pings) as $p) {
        $rrd_options .= ' DEF:pin' . $i . 'p' . $p . '=' . $filename . ':ping' . $p . ':AVERAGE';
        $rrd_options .= ' CDEF:p' . $i . 'p' . $p . '=pin' . $i . 'p' . $p . ',UN,0,pin' . $i . 'p' . $p . ',IF';
    }

    unset($pings_options, $m_options, $sdev_options);

    foreach (range(2, $pings) as $p) {
        $pings_options .= ',p' . $i . 'p' . $p . ',UN,+';
        $m_options .= ',p' . $i . 'p' . $p . ',+';
        $sdev_options .= ',p' . $i . 'p' . $p . ',m' . $i . ',-,DUP,*,+';
    }

    $rrd_options .= ' CDEF:pings' . $i . '=' . $pings . ',p' . $i . 'p1,UN' . $pings_options . ',-';
    $rrd_options .= ' CDEF:m' . $i . '=p' . $i . 'p1' . $m_options . ',pings' . $i . ',/';
    $rrd_options .= ' CDEF:sdev' . $i . '=p' . $i . 'p1,m' . $i . ',-,DUP,*' . $sdev_options . ',pings' . $i . ',/,SQRT';
    // end emulate Smokeping::calc_stddev
    $rrd_options .= " CDEF:dmlow$i=dm$i,sdev$i,2,/,-";
    $rrd_options .= " CDEF:s2d$i=sdev$i";
    $rrd_options .= " AREA:dmlow$i";
    $rrd_options .= " AREA:s2d$i#" . $colour . '30::STACK';
    $rrd_options .= " LINE1:dm$i#" . $colour . ":'$descr'";

    // $rrd_options .= " LINE1:sdev$i#000000:$descr";
    $rrd_options .= " VDEF:avmed$i=median$i,AVERAGE";
    $rrd_options .= " VDEF:avsd$i=sdev$i,AVERAGE";
    $rrd_options .= " CDEF:msr$i=median$i,POP,avmed$i,avsd$i,/";
    $rrd_options .= " VDEF:avmsr$i=msr$i,AVERAGE";

    $rrd_options .= " GPRINT:avmed$i:'%5.1lf%ss'";
    $rrd_options .= " GPRINT:ploss$i:AVERAGE:'%5.1lf%%'";

    $rrd_options .= " GPRINT:avsd$i:'%5.1lf%Ss'";
    $rrd_options .= " GPRINT:avmsr$i:'%5.1lf%s\\l'";

    $i++;
}//end foreach
