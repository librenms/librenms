<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage graphs
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

require 'includes/graphs/common.inc.php';

$stacked = generate_stacked_graphs();

if ($rrd_filename) {
    $rrd_filename_out = $rrd_filename;
    $rrd_filename_in = $rrd_filename;
}

if ($inverse) {
    $in = 'out';
    $out = 'in';
} else {
    $in = 'in';
    $out = 'out';
}

if ($multiplier) {
    $rrd_options .= ' DEF:p' . $out . 'octets=' . $rrd_filename_out . ':' . $ds_out . ':AVERAGE';
    $rrd_options .= ' DEF:p' . $in . 'octets=' . $rrd_filename_in . ':' . $ds_in . ':AVERAGE';
    $rrd_options .= ' DEF:p' . $out . 'octets_max=' . $rrd_filename_out . ':' . $ds_out . ':MAX';
    $rrd_options .= ' DEF:p' . $in . 'octets_max=' . $rrd_filename_in . ':' . $ds_in . ':MAX';
    $rrd_options .= " CDEF:inoctets=pinoctets,$multiplier,*";
    $rrd_options .= " CDEF:outoctets=poutoctets,$multiplier,*";
    $rrd_options .= " CDEF:inoctets_max=pinoctets_max,$multiplier,*";
    $rrd_options .= " CDEF:outoctets_max=poutoctets_max,$multiplier,*";
} else {
    $rrd_options .= ' DEF:' . $out . 'octets=' . $rrd_filename_out . ':' . $ds_out . ':AVERAGE';
    $rrd_options .= ' DEF:' . $in . 'octets=' . $rrd_filename_in . ':' . $ds_in . ':AVERAGE';
    $rrd_options .= ' DEF:' . $out . 'octets_max=' . $rrd_filename_out . ':' . $ds_out . ':MAX';
    $rrd_options .= ' DEF:' . $in . 'octets_max=' . $rrd_filename_in . ':' . $ds_in . ':MAX';
}

if ($_GET['previous'] == 'yes') {
    if ($multiplier) {
        $rrd_options .= ' DEF:p' . $out . 'octetsX=' . $rrd_filename_out . ':' . $ds_out . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:p' . $in . 'octetsX=' . $rrd_filename_in . ':' . $ds_in . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:p' . $out . "octetsX:$period";
        $rrd_options .= ' SHIFT:p' . $in . "octetsX:$period";
        $rrd_options .= " CDEF:inoctetsX=pinoctetsX,$multiplier,*";
        $rrd_options .= " CDEF:outoctetsX=poutoctetsX,$multiplier,*";
    } else {
        $rrd_options .= ' DEF:' . $out . 'octetsX=' . $rrd_filename_out . ':' . $ds_out . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:' . $in . 'octetsX=' . $rrd_filename_in . ':' . $ds_in . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $out . "octetsX:$period";
        $rrd_options .= ' SHIFT:' . $in . "octetsX:$period";
    }

    $rrd_options .= ' CDEF:octetsX=inoctetsX,outoctetsX,+';
    $rrd_options .= ' CDEF:doutoctetsX=outoctetsX,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:outbitsX=outoctetsX,8,*';
    $rrd_options .= ' CDEF:doutbitsX=doutoctetsX,8,*';
    $rrd_options .= ' CDEF:inbitsX=inoctetsX,8,*';
    $rrd_options .= ' VDEF:totinX=inoctetsX,TOTAL';
    $rrd_options .= ' VDEF:totoutX=outoctetsX,TOTAL';
    $rrd_options .= ' VDEF:totX=octetsX,TOTAL';
}

$rrd_options .= ' CDEF:octets=inoctets,outoctets,+';
$rrd_options .= ' CDEF:doutoctets=outoctets,' . $stacked['stacked'] . ',*';
$rrd_options .= ' CDEF:outbits=outoctets,8,*';
$rrd_options .= ' CDEF:outbits_max=outoctets_max,8,*';
$rrd_options .= ' CDEF:doutoctets_max=outoctets_max,' . $stacked['stacked'] . ',*';
$rrd_options .= ' CDEF:doutbits=doutoctets,8,*';
$rrd_options .= ' CDEF:doutbits_max=doutoctets_max,8,*';
$rrd_options .= ' CDEF:inbits=inoctets,8,*';
$rrd_options .= ' CDEF:inbits_max=inoctets_max,8,*';

if ($config['rrdgraph_real_percentile']) {
    $rrd_options .= ' CDEF:highbits=inoctets,outoctets,MAX,8,*';
    $rrd_options .= ' VDEF:percentilehigh=highbits,' . $config['percentile_value'] . ',PERCENT';
}

$rrd_options .= ' VDEF:totin=inoctets,TOTAL';
$rrd_options .= ' VDEF:totout=outoctets,TOTAL';
$rrd_options .= ' VDEF:tot=octets,TOTAL';
$rrd_options .= ' CDEF:dpercentile_outn=doutbits,' . $stacked['stacked'] . ',*';
$rrd_options .= ' VDEF:dpercentile_outnp=dpercentile_outn,' . $config['percentile_value'] . ',PERCENT';
$rrd_options .= ' CDEF:dpercentile_outnpn=doutbits,doutbits,-,dpercentile_outnp,' . $stacked['stacked'] . ',*,+';
$rrd_options .= ' VDEF:dpercentile_out=dpercentile_outnpn,FIRST';

if ($format == 'octets' || $format == 'bytes') {
    $rrd_options .= ' VDEF:percentile_in=inoctets,' . $config['percentile_value'] . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outoctets,' . $config['percentile_value'] . ',PERCENT';
    $units = 'Bps';
    $format = 'octets';
} else {
    $rrd_options .= ' VDEF:percentile_in=inbits,' . $config['percentile_value'] . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outbits,' . $config['percentile_value'] . ',PERCENT';
    $units = 'bps';
    $format = 'bits';
}

$rrd_options .= " COMMENT:'bps      Now       Ave      Max      " . $config['percentile_value'] . "th %\\n'";

$rrd_options .= ' AREA:in' . $format . '_max#D7FFC7' . $stacked['transparency'] . ':';
$rrd_options .= ' AREA:in' . $format . '#90B040' . $stacked['transparency'] . ':';
$rrd_options .= ' LINE:in' . $format . "#608720:'In '";
$rrd_options .= ' GPRINT:in' . $format . ':LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:in' . $format . ':AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:in' . $format . '_max:MAX:%6.2lf%s';
$rrd_options .= " GPRINT:percentile_in:%6.2lf%s\\n";

$rrd_options .= ' AREA:dout' . $format . '_max#E0E0FF' . $stacked['transparency'] . ':';
$rrd_options .= ' AREA:dout' . $format . '#8080C0' . $stacked['transparency'] . ':';
$rrd_options .= ' LINE:dout' . $format . "#606090:'Out'";
$rrd_options .= ' GPRINT:out' . $format . ':LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:out' . $format . ':AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:out' . $format . '_max:MAX:%6.2lf%s';
$rrd_options .= " GPRINT:percentile_out:%6.2lf%s\\n";

if ($config['rrdgraph_real_percentile']) {
    $rrd_options .= ' HRULE:percentilehigh#FF0000:"Highest"';
    $rrd_options .= " GPRINT:percentilehigh:\"%30.2lf%s\\n\"";
}

$rrd_options .= " GPRINT:tot:'Total %6.2lf%sB'";
$rrd_options .= " GPRINT:totin:'(In %6.2lf%sB'";
$rrd_options .= " GPRINT:totout:'Out %6.2lf%sB)\\l'";
$rrd_options .= ' LINE1:percentile_in#aa0000';
$rrd_options .= ' LINE1:dpercentile_out#aa0000';

if ($_GET['previous'] == 'yes') {
    $rrd_options .= ' LINE1.25:in' . $format . "X#009900:'Prev In \\\\n'";
    $rrd_options .= ' LINE1.25:dout' . $format . "X#000099:'Prev Out'";
}

unset($stacked);
