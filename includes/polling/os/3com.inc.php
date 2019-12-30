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
        $oids = ['stackUnitDesc.1', 'stackUnitPromVersion.1', 'stackUnitSWVersion.1', 'stackUnitSerialNumber.1','stackUnitCapabilities.1'];
        $data = snmp_get_multi($device, $oids, ['-OQUs','--hexOutputLength=0'], 'A3COM0352-STACK-CONFIG');
        $hardware .= ' ' . $data[1]['stackUnitDesc'];
        $version = $data[1]['stackUnitSWVersion'];
        $serial = $data[1]['stackUnitSerialNumber'];
        $features = $data[1]['stackUnitCapabilities'];
    }
}
