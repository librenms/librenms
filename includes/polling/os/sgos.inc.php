<?php
$version = trim(snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyVersion.0", "-OQv"), '"');
$hardware = trim(snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxySoftware.0", "-OQv"), '"');
$hostname = trim(snmp_get($device, "SNMPv2-MIB::sysName.0", "-OQv"), '"');
$sgos_requests = snmp_get($device, "BLUECOAT-SG-PROXY-MIB::sgProxyHttpClientRequestRate.0", "-OQvU");

if (is_numeric($sgos_requests)) {
    $rrd_def = 'DS:requests:GAUGE:600:0:U';
    $fields = array(
        'requests' => $sgos_requests
    );
    $tags = compact('rrd_def');
    data_update($device, 'sgos_average_requests', $tags, $fields);
    $graphs['sgos_average_requests'] = true;
}
