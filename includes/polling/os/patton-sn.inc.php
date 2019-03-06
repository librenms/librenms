<?php

//PATTON SmartNode
$oids = [
    'hw_release' => '.1.3.6.1.4.1.1768.100.1.3.0',
    'hw_version' => '.1.3.6.1.4.1.1768.100.1.4.0',
    'product_name' => '.1.3.6.1.4.1.1768.100.1.6.0',
    'serial' => '.1.3.6.1.4.1.1768.100.1.1.0',
    'version' => '.1.3.6.1.4.1.1768.100.1.5.0'
];
$os_data = snmp_get_multi_oid($device, $oids);
foreach ($oids as $var => $oid) {
    $$var = $os_data[$oid];
}

//Concatenate the hardware description
$hardware = $product_name . ', HwRel: ' .  $hw_release . ', HwVer: ' . $hw_version;
