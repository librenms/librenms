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

// FIXME str_pad really needs a "limit to length" so we can rid of all the substrs all over the code to limit the length as below...
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

    // FIXME: $descr unused? -- PDG 2015-11-14
    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($source, $descr_len);

    $filename = generate_smokeping_file($device, $filename);
    $rrd_options .= " DEF:median$i=" . $filename . ':median:AVERAGE ';
    $rrd_options .= " CDEF:dm$i=median$i,UN,0,median$i,IF";
    $rrd_options .= " DEF:loss$i=" . $filename . ':loss:AVERAGE';
    $rrd_options .= " CDEF:ploss$i=loss$i,$pings,/,100,*";
    // $rrd_options .= " CDEF:dm$i=median$i";
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

    $dm_list .= ",dm$i,+";
    $sd_list .= ",s2d$i,+";
    $ploss_list .= ",ploss$i,+";

    $i++;
}//end foreach

$descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr('Average', $descr_len);

$rrd_options .= ' CDEF:ploss_all=0' . $ploss_list . ",$i,/";
$rrd_options .= ' CDEF:dm_all=0' . $dm_list . ",$i,/";
// $rrd_options .= " CDEF:dm_all_clean=dm_all,UN,NaN,dm_all,IF";
$rrd_options .= ' CDEF:sd_all=0' . $sd_list . ",$i,/";
$rrd_options .= ' CDEF:dmlow_all=dm_all,sd_all,2,/,-';

$rrd_options .= ' AREA:dmlow_all';
$rrd_options .= ' AREA:sd_all#AAAAAA::STACK';
$rrd_options .= " LINE1:dm_all#CC0000:'$descr'";

$rrd_options .= ' VDEF:avmed=dm_all,AVERAGE';
$rrd_options .= ' VDEF:avsd=sd_all,AVERAGE';
$rrd_options .= ' CDEF:msr=dm_all,POP,avmed,avsd,/';
$rrd_options .= ' VDEF:avmsr=msr,AVERAGE';

$rrd_options .= " GPRINT:avmed:'%5.1lf%ss'";
$rrd_options .= " GPRINT:ploss_all:AVERAGE:'%5.1lf%%'";
$rrd_options .= " GPRINT:avsd:'%5.1lf%Ss'";
$rrd_options .= " GPRINT:avmsr:'%5.1lf%s\\l'";
