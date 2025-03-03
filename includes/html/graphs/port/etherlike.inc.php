<?php

// Cycle through dot3stats OIDs and build list of RRAs to pass to multi simplex grapher
$oids = [
    'dot3StatsAlignmentErrors',
    'dot3StatsFCSErrors',
    'dot3StatsSingleCollisionFrames',
    'dot3StatsMultipleCollisionFrames',
    'dot3StatsSQETestErrors',
    'dot3StatsDeferredTransmissions',
    'dot3StatsLateCollisions',
    'dot3StatsExcessiveCollisions',
    'dot3StatsInternalMacTransmitErrors',
    'dot3StatsCarrierSenseErrors',
    'dot3StatsFrameTooLongs',
    'dot3StatsInternalMacReceiveErrors',
    'dot3StatsSymbolErrors',
];

$i = 0;
$rrd_filename = get_port_rrdfile_path($device['hostname'], $port['port_id'], 'dot3');

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($oids as $oid) {
        $oid = str_replace('dot3Stats', '', $oid);
        $oid_ds = substr($oid, 0, 19);
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $oid;
        $rrd_list[$i]['ds'] = $oid_ds;
        $i++;
    }
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'Errors/sec';
$simple_rrd = 1;

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
