<?php

if ($this_port['dot3StatsIndex'] and $port['ifType'] == 'ethernetCsmacd') {
    $rrd_oldname= 'etherlike-'.$port['ifIndex']; // TODO: remove oldname check?
    $rrd_name = getPortRrdName($port_id, 'dot3');
    $rrd_def = array();

    $rrd_create = $config['rrd_rra'];
    foreach ($etherlike_oids as $oid) {
        $oid       = substr(str_replace('dot3Stats', '', $oid), 0, 19);
        $rrd_def[] = "DS:$oid:COUNTER:600:U:100000000000";
    }

    $fields = array();
    foreach ($etherlike_oids as $oid) {
        $data           = ($this_port[$oid] + 0);
        $fields[$oid] = $data;
    }

    $tags = compact('ifName', 'rrd_name', 'rrd_def', 'rrd_oldname');
    data_update($device, 'dot3', $tags, $fields);

    echo 'EtherLike ';
}
