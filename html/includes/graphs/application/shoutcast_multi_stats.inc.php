<?php

$device = device_by_id_cache($vars['id']);

// $colour     = "random";
$unit_text  = 'ShoutCast Server';
$total_text = 'Total of all ShoutCast Servers';
$nototal    = 0;

$rrddir = $config['rrd_dir'].'/'.$device['hostname'];
$files  = array();
$i      = 0;
$x      = 0;

if ($handle = opendir($rrddir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            if (eregi('app-shoutcast-'.$app['app_id'], $file)) {
                array_push($files, $file);
            }
        }
    }
}

foreach ($files as $id => $file) {
    $hostname                 = eregi_replace('app-shoutcast-'.$app['app_id'].'-', '', $file);
    $hostname                 = eregi_replace('.rrd', '', $hostname);
    list($host, $port)        = split('_', $hostname, 2);
    $rrd_filenames[]          = $rrddir.'/'.$file;
    $rrd_list[$i]['filename'] = $rrddir.'/'.$file;
    $rrd_list[$i]['descr']    = $host.':'.$port;
    $rrd_list[$i]['colour']   = $colour;
    $i++;
}

require 'includes/graphs/common.inc.php';

if ($width > '500') {
    $descr_len = 38;
}
else {
    $descr_len  = 8;
    $descr_len += round(($width - 250) / 8);
}

if ($width > '500') {
    $rrd_options .= ' COMMENT:"'.substr(str_pad($unit_text, ($descr_len + 2)), 0, ($descr_len + 2))."  Current  Unique  Average    Peak\\n\"";
}
else {
    $rrd_options .= ' COMMENT:"'.substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5))."  Now   Unique  Average    Peak\\n\"";
}

foreach ($rrd_list as $rrd) {
    $colours = (isset($rrd['colour']) ? $rrd['colour'] : 'default');
    $strlen  = ((strlen($rrd['descr']) < $descr_len) ? ($descr_len - strlen($rrd['descr'])) : '0');
    $descr   = (isset($rrd['descr']) ? rrdtool_escape($rrd['descr'], ($desc_len + $strlen)) : 'Unknown');
    for ($z = 0; $z < $strlen;
    $z++) {
        $descr .= ' ';
    }

    if ($i) {
        $stack = ':STACK';
    }

    $colour       = $config['graph_colours'][$colours][$x];
    $rrd_options .= ' DEF:cur'.$x.'='.$rrd['filename'].':current:AVERAGE';
    $rrd_options .= ' DEF:peak'.$x.'='.$rrd['filename'].':peak:MAX';
    $rrd_options .= ' DEF:unique'.$x.'='.$rrd['filename'].':unique:AVERAGE';
    $rrd_options .= ' VDEF:avg'.$x.'=cur'.$x.',AVERAGE';
    $rrd_options .= ' AREA:cur'.$x.'#'.$colour.":'".$descr."'$stack";
    $rrd_options .= ' GPRINT:cur'.$x.':LAST:"%6.2lf"';
    $rrd_options .= ' GPRINT:unique'.$x.':LAST:"%6.2lf%s"';
    $rrd_options .= ' GPRINT:avg'.$x.':"%6.2lf"';
    $rrd_options .= ' GPRINT:peak'.$x.':LAST:"%6.2lf"';
    $rrd_options .= " COMMENT:\"\\n\"";
    if ($x) {
        $totcur    .= ',cur'.$x.',+';
        $totpeak   .= ',peak'.$x.',+';
        $totunique .= ',unique'.$x.',+';
    }

    $x = (($x < count($config['graph_colours'][$colours]) - 1) ? $x + 1 : 0);
    // $x++;
}//end foreach

if (!$nototal) {
    $strlen = ((strlen($total_text) < $descr_len) ? ($descr_len - strlen($total_text)) : '0');
    $descr  = (isset($total_text) ? rrdtool_escape($total_text, ($desc_len + $strlen)) : 'Total');
    $colour = $config['graph_colours'][$colours][$x];
    for ($z = 0; $z < $strlen;
    $z++) {
        $descr .= ' ';
    }

    $rrd_options .= ' CDEF:totcur=cur0'.$totcur;
    $rrd_options .= ' CDEF:totunique=unique0'.$totunique;
    $rrd_options .= ' CDEF:totpeak=peak0'.$totpeak;
    $rrd_options .= ' VDEF:totavg=totcur,AVERAGE';
    $rrd_options .= ' LINE2:totcur#'.$colour.":'".$descr."'";
    $rrd_options .= ' GPRINT:totcur:LAST:"%6.2lf"';
    $rrd_options .= ' GPRINT:totunique:LAST:"%6.2lf%s"';
    $rrd_options .= ' GPRINT:totavg:"%6.2lf"';
    $rrd_options .= ' GPRINT:totpeak:LAST:"%6.2lf"';
    $rrd_options .= " COMMENT:\"\\n\"";
}
