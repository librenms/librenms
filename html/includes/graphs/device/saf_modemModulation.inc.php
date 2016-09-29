<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/saf.rrd';

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'db                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:modemModulation='.$rrdfilename.':modemModulation:AVERAGE ';
    $rrd_options .= " LINE1:modemModulation#CC0000:'Modulation                 ' ";
    $rrd_options .= ' GPRINT:modemModulation:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:modemModulation:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:modemModulation:MAX:%3.2lf\\\l ';
}
