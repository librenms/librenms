<?php

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

if ($this_port['dot3StatsIndex'] and $port['ifType'] == 'ethernetCsmacd') {
    $etherlike_oids = [
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

    $rrd_oldname = 'etherlike-' . $port['ifIndex']; // TODO: remove oldname check?
    $rrd_name = Rrd::portName($port_id, 'dot3');

    $rrd_def = new RrdDefinition();
    $fields = [];
    foreach ($etherlike_oids as $oid) {
        $oid_ds = str_replace('dot3Stats', '', $oid);
        $rrd_def->addDataset($oid_ds, 'COUNTER', null, 100000000000);

        $data = Number::cast($this_port[$oid]);
        $fields[$oid] = $data;
    }

    $tags = compact('ifName', 'rrd_name', 'rrd_def', 'rrd_oldname');
    app('Datastore')->put($device, 'dot3', $tags, $fields);

    echo 'EtherLike ';
}
