<?php

/**
 * Pure Storage Latency Graph
 * Display read/write latency in milliseconds
 */
$rrd_filename = Rrd::name($device['hostname'], 'purestorage_latency');

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " --title='Array Latency'";
    $rrd_options .= " --vertical-label='Latency (ms)'";
    $rrd_options .= ' --lower-limit=0';

    // Read latency (convert from microseconds to milliseconds)
    $rrd_options .= " DEF:readLatencyUS=$rrd_filename:read:AVERAGE";
    $rrd_options .= ' CDEF:readLatency=readLatencyUS,1000,/';
    $rrd_options .= " LINE2:readLatency#00AA00:'Read Latency'";
    $rrd_options .= " GPRINT:readLatency:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readLatency:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:readLatency:MAX:'Max\\:%8.2lf %s\\n'";

    // Write latency (convert from microseconds to milliseconds)
    $rrd_options .= " DEF:writeLatencyUS=$rrd_filename:write:AVERAGE";
    $rrd_options .= ' CDEF:writeLatency=writeLatencyUS,1000,/';
    $rrd_options .= " LINE2:writeLatency#0000AA:'Write Latency'";
    $rrd_options .= " GPRINT:writeLatency:LAST:'Last\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeLatency:AVERAGE:'Avg\\:%8.2lf %s'";
    $rrd_options .= " GPRINT:writeLatency:MAX:'Max\\:%8.2lf %s\\n'";
}
