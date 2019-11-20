<?php
// Grandstream HT
$oids = [
    'serial' => '.1.3.6.1.4.1.42397.1.2.1.0.0',
    'versionCore' => '.1.3.6.1.4.1.42397.1.2.3.2.0.0',
    'versionBase' => '.1.3.6.1.4.1.42397.1.2.3.3.0.0'
];
$os_data = snmp_get_multi_oid($device, $oids);
foreach ($oids as $var => $oid) {
    $$var = $os_data[$oid];
}
$version = 'Core: ' . $versionCore . ', Base: ' . $versionBase;
