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

use LibreNMS\Config;

require 'includes/html/graphs/common.inc.php';

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
        $rrd_options .= ' DEF:p' . $out . 'octets_maxX=' . $rrd_filename_out . ':' . $ds_out . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:p' . $in . 'octets_maxX=' . $rrd_filename_in . ':' . $ds_in . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:p' . $out . "octetsX:$period";
        $rrd_options .= ' SHIFT:p' . $in . "octetsX:$period";
        $rrd_options .= " CDEF:inoctetsX=pinoctetsX,$multiplier,*";
        $rrd_options .= " CDEF:outoctetsX=poutoctetsX,$multiplier,*";
        $rrd_options .= " CDEF:inoctets_maxX=pinoctets_maxX,$multiplier,*";
        $rrd_options .= " CDEF:outoctets_maxX=poutoctets_maxX,$multiplier,*";
    } else {
        $rrd_options .= ' DEF:' . $out . 'octetsX=' . $rrd_filename_out . ':' . $ds_out . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:' . $in . 'octetsX=' . $rrd_filename_in . ':' . $ds_in . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:' . $out . 'octets_maxX=' . $rrd_filename_out . ':' . $ds_out . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:' . $in . 'octets_maxX=' . $rrd_filename_in . ':' . $ds_in . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $out . "octetsX:$period";
        $rrd_options .= ' SHIFT:' . $in . "octetsX:$period";
    }

    $rrd_options .= ' CDEF:octetsX=inoctetsX,outoctetsX,+';
    $rrd_options .= ' CDEF:doutoctetsX=outoctetsX,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:outbitsX=outoctetsX,8,*';
    $rrd_options .= ' CDEF:outbits_maxX=outoctets_maxX,8,*';
    $rrd_options .= ' CDEF:doutoctets_maxX=outoctets_maxX,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:doutbitsX=doutoctetsX,8,*';
    $rrd_options .= ' CDEF:doutbits_maxX=doutoctets_maxX,8,*';
    $rrd_options .= ' CDEF:inbitsX=inoctetsX,8,*';
    $rrd_options .= ' CDEF:inbits_maxX=inoctets_maxX,8,*';
    $rrd_options .= ' VDEF:totinX=inoctetsX,TOTAL';
    $rrd_options .= ' VDEF:totoutX=outoctetsX,TOTAL';
    $rrd_options .= ' VDEF:totX=octetsX,TOTAL';
    $rrd_options .= ' CDEF:dpercentile_outnX=doutbitsX,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' VDEF:dpercentile_outnpX=dpercentile_outnX,' . Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' CDEF:dpercentile_outnpnX=doutbitsX,doutbitsX,-,dpercentile_outnpX,' . $stacked['stacked'] . ',*,+';
    $rrd_options .= ' VDEF:dpercentile_outX=dpercentile_outnpnX,FIRST';
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

if (Config::get('rrdgraph_real_percentile')) {
    $rrd_options .= ' CDEF:highbits=inoctets,outoctets,MAX,8,*';
    $rrd_options .= ' VDEF:percentilehigh=highbits,' . Config::get('percentile_value') . ',PERCENT';
    if ($_GET['previous'] == 'yes') {
        $rrd_options .= ' CDEF:highbitsX=inoctetsX,outoctetsX,MAX,8,*';
        $rrd_options .= ' VDEF:percentilehighX=highbitsX,' . Config::get('percentile_value') . ',PERCENT';
    }
}

$rrd_options .= ' VDEF:totin=inoctets,TOTAL';
$rrd_options .= ' VDEF:totout=outoctets,TOTAL';
$rrd_options .= ' VDEF:tot=octets,TOTAL';
$rrd_options .= ' CDEF:dpercentile_outn=doutbits,' . $stacked['stacked'] . ',*';
$rrd_options .= ' VDEF:dpercentile_outnp=dpercentile_outn,' . Config::get('percentile_value') . ',PERCENT';
$rrd_options .= ' CDEF:dpercentile_outnpn=doutbits,doutbits,-,dpercentile_outnp,' . $stacked['stacked'] . ',*,+';
$rrd_options .= ' VDEF:dpercentile_out=dpercentile_outnpn,FIRST';

if ($format == 'octets' || $format == 'bytes') {
    $rrd_options .= ' VDEF:percentile_in=inoctets,' . Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outoctets,' . Config::get('percentile_value') . ',PERCENT';
    if ($_GET['previous'] == 'yes') {
        $rrd_options .= ' VDEF:percentile_inX=inoctetsX,' . Config::get('percentile_value') . ',PERCENT';
        $rrd_options .= ' VDEF:percentile_outX=outoctetsX,' . Config::get('percentile_value') . ',PERCENT';
    }
    $units = 'Bps';
    $format = 'octets';
} else {
    $rrd_options .= ' VDEF:percentile_in=inbits,' . Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outbits,' . Config::get('percentile_value') . ',PERCENT';
    if ($_GET['previous'] == 'yes') {
        $rrd_options .= ' VDEF:percentile_inX=inbitsX,' . Config::get('percentile_value') . ',PERCENT';
        $rrd_options .= ' VDEF:percentile_outX=outbitsX,' . Config::get('percentile_value') . ',PERCENT';
    }
    $units = 'bps';
    $format = 'bits';
}

$rrd_options .= " COMMENT:'bps      Now       Ave      Max      " . Config::get('percentile_value') . "th %\\n'";

$rrd_options .= ' AREA:in' . $format . '_max#' . Config::get('graph_colours.generic.area_in_max') . $stacked['transparency'] . ':';
$rrd_options .= ' AREA:in' . $format . '#' . Config::get('graph_colours.generic.area_in') . $stacked['transparency'] . ':';
$rrd_options .= ' LINE:in' . $format . '#' . Config::get('graph_colours.generic.line_in') . ":'In '";
$rrd_options .= ' GPRINT:in' . $format . ':LAST:%6.'.$float_precision.'lf%s';
$rrd_options .= ' GPRINT:in' . $format . ':AVERAGE:%6.'.$float_precision.'lf%s';
$rrd_options .= ' GPRINT:in' . $format . '_max:MAX:%6.'.$float_precision.'lf%s';
$rrd_options .= " GPRINT:percentile_in:%6.".$float_precision."lf%s\\n";

$rrd_options .= ' AREA:dout' . $format . '_max#' . Config::get('graph_colours.generic.area_out_max') . $stacked['transparency'] . ':';
$rrd_options .= ' AREA:dout' . $format . '#' . Config::get('graph_colours.generic.area_out') . $stacked['transparency'] . ':';
$rrd_options .= ' LINE:dout' . $format . '#' . Config::get('graph_colours.generic.line_out') . ":'In '";
$rrd_options .= ' GPRINT:out' . $format . ':LAST:%6.'.$float_precision.'lf%s';
$rrd_options .= ' GPRINT:out' . $format . ':AVERAGE:%6.'.$float_precision.'lf%s';
$rrd_options .= ' GPRINT:out' . $format . '_max:MAX:%6.'.$float_precision.'lf%s';
$rrd_options .= " GPRINT:percentile_out:%6.".$float_precision."lf%s\\n";

if (Config::get('rrdgraph_real_percentile')) {
    $rrd_options .= ' HRULE:percentilehigh#' . Config::get('graph_colours.generic.percentile_high') . ':"Highest"';
    $rrd_options .= " GPRINT:percentilehigh:\"%30.".$float_precision."lf%s\\n\"";
}

$rrd_options .= " GPRINT:tot:'Total %6.".$float_precision."lf%sB'";
$rrd_options .= " GPRINT:totin:'(In %6.".$float_precision."lf%sB'";
$rrd_options .= " GPRINT:totout:'Out %6.".$float_precision."lf%sB)\\l'";
$rrd_options .= ' LINE1:percentile_in#' . Config::get('graph_colours.generic.percentile_in');
$rrd_options .= ' LINE1:dpercentile_out#' . Config::get('graph_colours.generic.percentile_out');

// Linear prediction of trend
if ($to > time()) {
    $rrd_options .= ' VDEF:islope=inbits_max,LSLSLOPE';
    $rrd_options .= ' VDEF:icons=inbits_max,LSLINT';
    $rrd_options .= ' CDEF:ilsl=inbits_max,POP,islope,COUNT,*,icons,+ ';
    $rrd_options .= " LINE2:ilsl#". Config::get('graph_colours.generic.lp.in') .":'In Linear Prediction\\n':dashes=8";

    $rrd_options .= ' VDEF:oslope=doutbits_max,LSLSLOPE';
    $rrd_options .= ' VDEF:ocons=doutbits_max,LSLINT';
    $rrd_options .= ' CDEF:olsl=doutbits_max,POP,oslope,COUNT,*,ocons,+ ';
    $rrd_options .= " LINE2:olsl#". Config::get('graph_colours.generic.lp.out') .":'In Linear Prediction\\n':dashes=8";
}

if ($_GET['previous'] == 'yes') {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= ' LINE1.25:in' . $format . "X#" . Config::get('graph_colours.generic.lp.previous.in') . ":'Prev In '\t";
    $rrd_options .= ' GPRINT:in' . $format . 'X:AVERAGE:%6.'.$float_precision.'lf%s';
    $rrd_options .= ' GPRINT:in' . $format . '_maxX:MAX:%6.'.$float_precision.'lf%s';
    $rrd_options .= " GPRINT:percentile_inX:%6.".$float_precision."lf%s\\n";
    $rrd_options .= ' LINE1.25:dout' . $format . "X#" . Config::get('graph_colours.generic.lp.previous.out') . ":'Prev Out '\t";
    $rrd_options .= ' GPRINT:out' . $format . 'X:AVERAGE:%6.'.$float_precision.'lf%s';
    $rrd_options .= ' GPRINT:out' . $format . '_maxX:MAX:%6.'.$float_precision.'lf%s';
    $rrd_options .= " GPRINT:percentile_outX:%6.".$float_precision."lf%s\\n";
    $rrd_options .= " GPRINT:totX:'Total %6.".$float_precision."lf%sB'";
    $rrd_options .= " GPRINT:totinX:'(In %6.".$float_precision."lf%sB'";
    $rrd_options .= " GPRINT:totoutX:'Out %6.".$float_precision."lf%sB)\\l'";
    $rrd_options .= ' LINE1:percentile_inX#' . Config::get('graph_colours.generic.lp.previous.percentile_in');
    $rrd_options .= ' LINE1:dpercentile_outX#' . Config::get('graph_colours.generic.lp.previous.percentile_out');
}

unset($stacked);
