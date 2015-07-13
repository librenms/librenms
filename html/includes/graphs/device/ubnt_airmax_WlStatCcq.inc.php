<?php

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/ubnt-airmax-mib.rrd';

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Percent                    Now    Min     Max\\n'";
    $rrd_options .= ' DEF:WlStatCcq='.$rrdfilename.':WlStatCcq:AVERAGE ';
    $rrd_options .= " LINE1:WlStatCcq#CC0000:'CCQ                  ' ";
    $rrd_options .= ' GPRINT:WlStatCcq:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:WlStatCcq:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:WlStatCcq:MAX:%3.2lf\\\l ';
}
