<?php

if (strpos($device['sysDescr'], 'Software')) {
    $hardware = str_replace("3Com ", '', substr($device['sysDescr'], 0, strpos($device['sysDescr'], 'Software')));
    // Version is the last word in the sysDescr's first line
    list($version) = explode("\n", substr($device['sysDescr'], (strpos($device['sysDescr'], 'Version') + 8)));
} else {
    $hardware = str_replace("3Com ", '', $device['sysDescr']);
    $version = '';
    // Old Stack Units
    if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.43.10.27.4.1.')) {
        //$oids = ['stackUnitDesc.1', 'stackUnitPromVersion.1', 'stackUnitSWVersion.1', 'stackUnitSerialNumber.1','stackUnitCapabilities.1']; //A3COM0352-STACK-CONFIG
        $oids = ['.1.3.6.1.4.1.43.10.27.1.1.1.5.1', '.1.3.6.1.4.1.43.10.27.1.1.1.10.1', '.1.3.6.1.4.1.43.10.27.1.1.1.12.1', '.1.3.6.1.4.1.43.10.27.1.1.1.13.1','.1.3.6.1.4.1.43.10.27.1.1.1.9.1'];
        $data = snmp_get_multi_oid($device, $oids, ['-OQUn','--hexOutputLength=0'], '');
        print_r($data);
        $hardware .= ' ' . $data['.1.3.6.1.4.1.43.10.27.1.1.1.5.1'];
        $version = $data['.1.3.6.1.4.1.43.10.27.1.1.1.12.1'];
        $serial = $data['.1.3.6.1.4.1.43.10.27.1.1.1.13.1'];
        $features = $data['.1.3.6.1.4.1.43.10.27.1.1.1.9.1'];
    }
}
