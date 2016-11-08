<?php
//
// Poll information of Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    list(,,,$hardware,$version,) = explode(' ', $poll_device['sysDescr']);
    $serial = snmp_get($device, 'trpzSerialNumber.0', '-OQv', 'TRAPEZE-NETWORKS-BASIC-MIB', 'trapeze');
    $version = snmp_get($device, 'trpzVersionString.0', '-OQv', 'TRAPEZE-NETWORKS-BASIC-MIB', 'trapeze');
    $domain = snmp_get($device, 'trpzMobilityDomainName.0', '-OQv', 'TRAPEZE-NETWORKS-BASIC-MIB', 'trapeze');

    if ($domain) {
       $features = "Cluster: $domain";
    }
}
