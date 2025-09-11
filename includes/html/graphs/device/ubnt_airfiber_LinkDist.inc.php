<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfiber-mib');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Metres                     Now    Min     Max\\n'";
    $rrd_options .= ' DEF:radioLinkDistM=' . $rrdfilename . ':radioLinkDistM:AVERAGE ';
    $rrd_options .= " LINE1:radioLinkDistM#CC0000:'Distance             ' ";
    $rrd_options .= ' GPRINT:radioLinkDistM:LAST:%3.2lf%s ';
    $rrd_options .= ' GPRINT:radioLinkDistM:MIN:%3.2lf%s ';
    $rrd_options .= ' GPRINT:radioLinkDistM:MAX:%3.2lf%s\\\l ';
}
