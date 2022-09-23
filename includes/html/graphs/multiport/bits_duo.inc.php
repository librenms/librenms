<?php

if ($_GET['legend']) {
    $legend = $_GET['legend'];
}

$rrd_options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height ";
$rrd_options .= \LibreNMS\Config::get('rrdgraph_def_text') . ' -c FONT#' . ltrim(\LibreNMS\Config::get('rrdgraph_def_text_color'), '#');
if ($height < '99') {
    $rrd_options .= ' --only-graph';
}

$i = 1;

foreach (explode(',', $_GET['id']) as $ifid) {
    $int = dbFetchRow('SELECT `hostname` FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id', [$ifid]);
    $rrd_file = get_port_rrdfile_path($int['hostname'], $ifid);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_options .= ' DEF:inoctets' . $i . '=' . $rrd_file . ':INOCTETS:AVERAGE';
        $rrd_options .= ' DEF:outoctets' . $i . '=' . $rrd_file . ':OUTOCTETS:AVERAGE';
        $in_thing .= $seperator . 'inoctets' . $i . ',UN,0,' . 'inoctets' . $i . ',IF';
        $out_thing .= $seperator . 'outoctets' . $i . ',UN,0,' . 'outoctets' . $i . ',IF';
        $pluses .= $plus;
        $seperator = ',';
        $plus = ',+';
        $i++;
    }
}

unset($seperator);
unset($plus);

foreach (explode(',', $_GET['idb']) as $ifid) {
    $int = dbFetchRow('SELECT `hostname` FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id', [$ifid]);
    $rrd_file = get_port_rrdfile_path($int['hostname'], $ifid);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_options .= ' DEF:inoctetsb' . $i . '=' . $rrd_file . ':INOCTETS:AVERAGE';
        $rrd_options .= ' DEF:outoctetsb' . $i . '=' . $rrd_file . ':OUTOCTETS:AVERAGE';
        $in_thingb .= $seperator . 'inoctetsb' . $i . ',UN,0,' . 'inoctetsb' . $i . ',IF';
        $out_thingb .= $seperator . 'outoctetsb' . $i . ',UN,0,' . 'outoctetsb' . $i . ',IF';
        $plusesb .= $plus;
        $seperator = ',';
        $plus = ',+';
        $i++;
    }
}

if ($inverse) {
    $in = 'out';
    $out = 'in';
} else {
    $in = 'in';
    $out = 'out';
}

$rrd_options .= ' CDEF:' . $in . 'octets=' . $in_thing . $pluses;
$rrd_options .= ' CDEF:' . $out . 'octets=' . $out_thing . $pluses;
$rrd_options .= ' CDEF:' . $in . 'octetsb=' . $in_thingb . $plusesb;
$rrd_options .= ' CDEF:' . $out . 'octetsb=' . $out_thingb . $plusesb;
$rrd_options .= ' CDEF:doutoctets=outoctets,-1,*';
$rrd_options .= ' CDEF:inbits=inoctets,8,*';
$rrd_options .= ' CDEF:outbits=outoctets,8,*';
$rrd_options .= ' CDEF:doutbits=doutoctets,8,*';
$rrd_options .= ' CDEF:doutoctetsb=outoctetsb,-1,*';
$rrd_options .= ' CDEF:inbitsb=inoctetsb,8,*';
$rrd_options .= ' CDEF:outbitsb=outoctetsb,8,*';
$rrd_options .= ' CDEF:doutbitsb=doutoctetsb,8,*';
$rrd_options .= ' CDEF:inbits_tot=inbits,inbitsb,+';
$rrd_options .= ' CDEF:outbits_tot=outbits,outbitsb,+';
$rrd_options .= ' CDEF:doutbits_tot=outbits_tot,-1,*';
$rrd_options .= ' CDEF:nothing=outbits_tot,outbits_tot,-';

if ($legend == 'no') {
    $rrd_options .= ' AREA:inbits_tot#cdeb8b:';
    $rrd_options .= ' AREA:inbits#ffcc99:';
    $rrd_options .= ' AREA:doutbits_tot#C3D9FF:';
    $rrd_options .= ' AREA:doutbits#ffcc99:';
    $rrd_options .= ' LINE1:inbits#aa9966:';
    $rrd_options .= ' LINE1:doutbits#aa9966:';
    // $rrd_options .= " LINE1:inbitsb#006600:";
    // $rrd_options .= " LINE1:doutbitsb#000066:";
    $rrd_options .= ' LINE1.25:inbits_tot#006600:';
    $rrd_options .= ' LINE1.25:doutbits_tot#000099:';
    $rrd_options .= ' LINE0.5:nothing#555555:';
} else {
    $rrd_options .= " COMMENT:bps\ \ \ \ \ \ \ \ \ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Min\ \ \ \ \ \ Max\\\\n";
    $rrd_options .= ' AREA:inbits_tot#cdeb8b:Peering\ In\ ';
    $rrd_options .= ' GPRINT:inbitsb:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbitsb:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbitsb:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbitsb:MAX:%6.2lf%s\l';
    $rrd_options .= ' AREA:doutbits_tot#C3D9FF:';
    $rrd_options .= ' COMMENT:\ \ \ \ \ \ \ \ \ \ Out';
    $rrd_options .= ' GPRINT:outbitsb:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbitsb:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbitsb:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbitsb:MAX:%6.2lf%s\l';

    $rrd_options .= ' AREA:inbits#ffcc99:Transit\ In\ ';
    $rrd_options .= ' GPRINT:inbits:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits:MAX:%6.2lf%s\l';
    $rrd_options .= ' AREA:doutbits#ffcc99:';
    $rrd_options .= ' COMMENT:\ \ \ \ \ \ \ \ \ \ Out';
    $rrd_options .= ' GPRINT:outbits:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits:MAX:%6.2lf%s\l';

    $rrd_options .= ' COMMENT:Total\ \ \ \ \ In\ ';
    $rrd_options .= ' GPRINT:inbits_tot:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits_tot:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits_tot:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:inbits_tot:MAX:%6.2lf%s\l';
    $rrd_options .= ' COMMENT:\ \ \ \ \ \ \ \ \ \ Out';
    $rrd_options .= ' GPRINT:outbits_tot:LAST:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits_tot:AVERAGE:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits_tot:MIN:%6.2lf%s';
    $rrd_options .= ' GPRINT:outbits_tot:MAX:%6.2lf%s\l';

    $rrd_options .= ' LINE1:inbits#aa9966:';
    $rrd_options .= ' LINE1:doutbits#aa9966:';
    // $rrd_options .= " LINE1.25:inbitsb#006600:";
    // $rrd_options .= " LINE1.25:doutbitsb#006600:";
    $rrd_options .= ' LINE1.25:inbits_tot#006600:';
    $rrd_options .= ' LINE1.25:doutbits_tot#000099:';
    $rrd_options .= ' LINE0.5:nothing#555555:';
}//end if

if ($width <= '300') {
    $rrd_options .= ' --font LEGEND:7:' . \LibreNMS\Config::get('mono_font') . ' --font AXIS:6:' . \LibreNMS\Config::get('mono_font') . ' --font-render-mode normal';
}
