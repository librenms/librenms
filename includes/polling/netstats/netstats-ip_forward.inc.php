<?php
use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], array('Snom', 'asa'))) {
    echo ' IP-FORWARD';

    $oid = 'ipCidrRouteNumber';
    $fields = array();
    $rrd_def = RrdDefinition::make()->addDataset($oid, 'GAUGE', null, 5000000);
    $data = snmp_get($device, 'IP-FORWARD-MIB::' . $oid . '.0', '-OQv');
    if (is_numeric($data)) {
        $value = $data;
        $fields[$oid] = $value;
        $tags = compact('rrd_def');
        data_update($device, 'netstats-ip_forward', $tags, $fields);
        $graphs['netstat_ip_forward'] = true;
    }
}
unset($oid, $rrd_def, $data, $fields, $tags);
