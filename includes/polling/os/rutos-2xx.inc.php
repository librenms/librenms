<?php
$hardware = trim(snmp_get($device, '1.3.6.1.4.1.48690.1.7.0', '-OQv', '', ''), '"');
$version = trim(snmp_get($device, '1.3.6.1.4.1.48690.2.14.0', '-OQv', '', ''), '"');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.48690.1.5.0', '-OQv', '', ''), '"');

use LibreNMS\RRD\RrdDefinition;

# Mobile Data Usage
$tlt_array = array(
    '.1.3.6.1.4.1.48690.2.11.0',
    '.1.3.6.1.4.1.48690.2.10.0',
);
$usage = snmp_get_multi_oid($device, $tlt_array);
$bw_send = $usage['.1.3.6.1.4.1.48690.2.11.0'];
$bw_received = $usage['.1.3.6.1.4.1.48690.2.10.0'];
if ($bw_send >= 0 && $bw_received >= 0) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('bw_send', 'GAUGE', 0)
        ->addDataset('bw_received', 'GAUGE', 0);

    $fields = array(
        'bw_send' => $bw_send,
        'bw_received' => $bw_received,
    );
    $tags = compact('rrd_def');
    data_update($device, 'rutos_2xx_mobileDataUsage', $tags, $fields);
    $graphs['rutos_2xx_mobileDataUsage'] = true;
}