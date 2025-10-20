<?php

/**
 * Pure Storage Bandwidth Graph
 * Display read/write bandwidth in bits per second
 */
$rrd_filename = Rrd::name($device['hostname'], 'purestorage_bandwidth');

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " --title='Array Bandwidth'";
    $rrd_options .= " --vertical-label='bits/sec'";
    $rrd_options .= " --lower-limit=0";

    // Read bandwidth (converted from bytes to bits)
    $rrd_options .= " DEF:readBytes=$rrd_filename:read:AVERAGE";
    $rrd_options .= " CDEF:readBits=readBytes,8,*";
    $rrd_options .= " LINE2:readBits#00AA00:'Read Bandwidth'";
    $rrd_options .= " GPRINT:readBits:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readBits:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readBits:MAX:'Max\\:%8.2lf %s\\n'";

    // Write bandwidth (converted from bytes to bits)
    $rrd_options .= " DEF:writeBytes=$rrd_filename:write:AVERAGE";
    $rrd_options .= " CDEF:writeBits=writeBytes,8,*";
    $rrd_options .= " LINE2:writeBits#0000AA:'Write Bandwidth'";
    $rrd_options .= " GPRINT:writeBits:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeBits:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeBits:MAX:'Max\\:%8.2lf %s\\n'";
}
