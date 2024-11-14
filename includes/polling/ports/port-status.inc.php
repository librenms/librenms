<?php

use LibreNMS\RRD\RrdDefinition;

$rrd_name = Rrd::portName($port_id, 'status');
$rrd_def = RrdDefinition::make()
    ->addDataset('ifOperStatus', 'GAUGE', 0);

$upd = "$polled:" . $this_port['ifOperStatus'];

$fields = [
    'Status' => $this_port['ifOperStatus'],
];

$tags = compact('ifName', 'rrd_name', 'rrd_def');
data_update($device, 'status', $tags, $fields);
echo 'Status ';
