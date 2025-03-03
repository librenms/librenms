<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_drops');

$i = 0;

foreach ([
    'badoffset' => 'BadOffset',
    'fragmented' => 'Fragmented',
    'short' => 'Short',
    'normalized' => 'Normalized',
    'memory' => 'Memory',
    'timestamp' => 'Timestamp',
    'congestion' => 'Congestion',
    'ipoption' => 'IpOption',
    'protocksum' => 'ProtoCksum',
    'statemismatch' => 'StateMismatch',
    'stateinsert' => 'StateInsert',
    'statelimit' => 'StateLimit',
    'srclimit' => 'SrcLimit',
    'synproxy' => 'Synproxy',
    'translate' => 'Translate',
    'noroute' => 'NoRoute',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Dropped Packets';

$units = 'Packets';
$total_units = 'Packets';
$colours = 'psychedelic';

$scale_min = '0';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
