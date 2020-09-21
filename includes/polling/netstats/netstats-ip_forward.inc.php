<?php

use Illuminate\Support\Str;
use LibreNMS\RRD\RrdDefinition;

if (! Str::startsWith($device['os'], ['Snom', 'asa'])) {
    echo ' IP-FORWARD';

    $oid = 'ipCidrRouteNumber';
    $fields = [];
    $rrd_def = RrdDefinition::make()->addDataset($oid, 'GAUGE', null, 5000000);
    $data = snmp_get($device, 'IP-FORWARD-MIB::' . $oid . '.0', '-OQv');
    if (is_numeric($data)) {
        $value = $data;
        $fields[$oid] = $value;
        $tags = compact('rrd_def');
        data_update($device, 'netstats-ip_forward', $tags, $fields);
        $os->enableGraph('netstat_ip_forward');
    }
}
unset($oid, $rrd_def, $data, $fields, $tags);
