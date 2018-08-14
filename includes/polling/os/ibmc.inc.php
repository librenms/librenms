<?php

$oids = array (
    'version' => 'firmwareVersion.11.65.99.116.105.118.101.32.105.66.77.67',   // firmwareVersion."Active iBMC"
    'hardware'   => 'deviceName.0',
    'serial' => 'deviceSerialNo.0'
);

$data = snmp_get_multi_oid($device, $oids, '-OUQs', 'HUAWEI-SERVER-IBMC-MIB');

foreach ($oids as $var => $oid) {
    $$var = $data[$oid];
}

unset($data, $oids);
