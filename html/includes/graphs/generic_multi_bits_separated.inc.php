<?php

require 'includes/graphs/common.inc.php';

$i = 0;
if ($width > '500') {
    $descr_len = 18;
}
else {
    $descr_len  = 8;
    $descr_len += round(($width - 260) / 9.5);
}

$unit_text = 'Bits/sec';

if (!$noagg || !$nodetails) {
    if ($width > '500') {
        $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5))."    Current      Average     Maximum    '";
        if (!$nototal) {
            $rrd_options .= " COMMENT:'Total      '";
        }

        $rrd_options .= " COMMENT:'\l'";
    }
    else {
        $nototal      = true;
        $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5))."     Now         Ave          Max\l'";
    }
}

if (!isset($multiplier)) {
    $multiplier = '8';
}

foreach ($rrd_list as $rrd) {
    if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) {
        $iter = 0;
    }

    $colour_in  = $config['graph_colours'][$colours_in][$iter];
    $colour_out = $config['graph_colours'][$colours_out][$iter];

    if (!$nodetails) {
        if (isset($rrd['descr_in'])) {
            $descr = rrdtool_escape($rrd['descr_in'], $descr_len).'  In';
        }
        else {
            $descr = rrdtool_escape($rrd['descr'], $descr_len).'  In';
        }

        $descr_out = rrdtool_escape($rrd['descr_out'], $descr_len).' Out';
    }

    $rrd_options .= ' DEF:'.$in.$i.'='.$rrd['filename'].':'.$ds_in.':AVERAGE ';
    $rrd_options .= ' DEF:'.$out.$i.'='.$rrd['filename'].':'.$ds_out.':AVERAGE ';
    $rrd_options .= ' CDEF:inB'.$i.'=in'.$i.",$multiplier,* ";
    $rrd_options .= ' CDEF:outB'.$i.'=out'.$i.",$multiplier,*";
    $rrd_options .= ' CDEF:outB'.$i.'_neg=outB'.$i.',-1,*';
    $rrd_options .= ' CDEF:octets'.$i.'=inB'.$i.',outB'.$i.',+';

    if (!$nototal) {
        $rrd_options .= ' VDEF:totin'.$i.'=inB'.$i.',TOTAL';
        $rrd_options .= ' VDEF:totout'.$i.'=outB'.$i.',TOTAL';
        $rrd_options .= ' VDEF:tot'.$i.'=octets'.$i.',TOTAL';
    }

    if ($i) {
        $stack = ':STACK';
    }

    $rrd_options .= ' AREA:inB'.$i.'#'.$colour_in.":'".$descr."'$stack";
    if (!$nodetails) {
        $rrd_options .= ' GPRINT:inB'.$i.":LAST:%6.2lf%s$units";
        $rrd_options .= ' GPRINT:inB'.$i.":AVERAGE:%6.2lf%s$units";
        $rrd_options .= ' GPRINT:inB'.$i.":MAX:%6.2lf%s$units";
        if (!$nototal) {
            $rrd_options .= ' GPRINT:totin'.$i.":%6.2lf%s$total_units";
        }

        $rrd_options .= '\l';
    }

    $rrd_options  .= " 'HRULE:0#".$colour_out.':'.$descr_out."'";
    $rrd_optionsb .= " 'AREA:outB".$i.'_neg#'.$colour_out.":$stack'";

    if (!$nodetails) {
        $rrd_options .= ' GPRINT:outB'.$i.":LAST:%6.2lf%s$units";
        $rrd_options .= ' GPRINT:outB'.$i.":AVERAGE:%6.2lf%s$units";
        $rrd_options .= ' GPRINT:outB'.$i.":MAX:%6.2lf%s$units";
        if (!$nototal) {
            $rrd_options .= ' GPRINT:totout'.$i.":%6.2lf%s$total_unit";
        }

        $rrd_options .= '\l';
    }

    $rrd_options .= " 'COMMENT:\l'";

    if ($i >= 1) {
        $aggr_in  .= ',';
        $aggr_out .= ',';
    }

    if ($i > 1) {
        $aggr_in  .= 'ADDNAN,';
        $aggr_out .= 'ADDNAN,';
    }

    $aggr_in  .= $in.$i;
    $aggr_out .= $out.$i;

    $i++;
    $iter++;
}

if (!$noagg) {
    $rrd_options .= ' CDEF:aggr'.$in.'bytes='.$aggr_in.',ADDNAN';
    $rrd_options .= ' CDEF:aggr'.$out.'bytes='.$aggr_out.',ADDNAN';
    $rrd_options .= ' CDEF:aggrinbits=aggrinbytes,'.$multiplier.',*';
    $rrd_options .= ' CDEF:aggroutbits=aggroutbytes,'.$multiplier.',*';
    $rrd_options .= ' VDEF:totalin=aggrinbytes,TOTAL';
    $rrd_options .= ' VDEF:totalout=aggroutbytes,TOTAL';
    $rrd_options .= " COMMENT:' \\\\n'";
    $rrd_options .= " COMMENT:'".substr(str_pad('Aggregate In', ($descr_len + 5)), 0, ($descr_len + 5))."'";
    $rrd_options .= " GPRINT:aggrinbits:LAST:%6.2lf%s$units";
    $rrd_options .= " GPRINT:aggrinbits:AVERAGE:%6.2lf%s$units";
    $rrd_options .= " GPRINT:aggrinbits:MAX:%6.2lf%s$units";
    if (!$nototal) {
        $rrd_options .= " GPRINT:totalin:%6.2lf%s$total_units";
    }

    $rrd_options .= "\\\\n";
    $rrd_options .= " COMMENT:'".substr(str_pad('Aggregate Out', ($descr_len + 5)), 0, ($descr_len + 5))."'";
    $rrd_options .= " GPRINT:aggroutbits:LAST:%6.2lf%s$units";
    $rrd_options .= " GPRINT:aggroutbits:AVERAGE:%6.2lf%s$units";
    $rrd_options .= " GPRINT:aggroutbits:MAX:%6.2lf%s$units";
    if (!$nototal) {
        $rrd_options .= " GPRINT:totalout:%6.2lf%s$total_units";
    }

    $rrd_options .= "\\\\n";
}

if ($custom_graph) {
    $rrd_options .= $custom_graph;
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#999999';
