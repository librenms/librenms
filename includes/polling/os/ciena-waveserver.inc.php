<?php
// Grandstream HT
$oids = [
    'serial' => '.1.3.6.1.2.1.47.1.1.1.1.11.1',
    'version' => '.1.3.6.1.4.1.1271.3.4.14.3.1.5.0',
    'hardware' => '.1.3.6.1.4.1.1271.3.4.6.3.1.3.0'
];
$os_data = snmp_get_multi_oid($device, $oids);
foreach ($oids as $var => $oid) {
    $$var = $os_data[$oid];
}
