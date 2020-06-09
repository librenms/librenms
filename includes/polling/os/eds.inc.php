<?php

//EDS-MIB::eCompanyName.0 = STRING: "Embedded Data Systems"
//EDS-MIB::eProductName.0 = STRING: "Ethernet to 1-wire Interface"
//EDS-MIB::eFirmwareVersion.0 = STRING: "2.10"
//EDS-MIB::eFirmwareDate.0 = STRING: "May 17 2017"

$oids = array (
    'version' => 'eFirmwareVersion.0',
    'hardware'   => 'eProductName.0'
);

$data = snmp_get_multi_oid($device, $oids, '-OUQs', 'EDS-MIB');

foreach ($oids as $var => $oid) {
    $$var = $data[$oid];
}

unset($data, $oids);
