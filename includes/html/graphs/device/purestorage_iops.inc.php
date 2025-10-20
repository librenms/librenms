<?php

/**
 * Pure Storage IOPS Graph
 * Display read/write operations per second
 */
$rrd_filename = Rrd::name($device['hostname'], 'purestorage_iops');

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " --title='Array IOPS'";
    $rrd_options .= " --vertical-label='Operations/sec'";
    $rrd_options .= ' --lower-limit=0';

    // Read IOPS
    $rrd_options .= " DEF:readIOPS=$rrd_filename:read:AVERAGE";
    $rrd_options .= " LINE2:readIOPS#00AA00:'Read IOPS'";
    $rrd_options .= " GPRINT:readIOPS:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readIOPS:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readIOPS:MAX:'Max\\:%8.2lf %s\\n'";

    // Write IOPS
    $rrd_options .= " DEF:writeIOPS=$rrd_filename:write:AVERAGE";
    $rrd_options .= " LINE2:writeIOPS#0000AA:'Write IOPS'";
    $rrd_options .= " GPRINT:writeIOPS:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeIOPS:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeIOPS:MAX:'Max\\:%8.2lf %s\\n'";
}
