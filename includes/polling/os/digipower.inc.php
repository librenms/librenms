<?php

$oids = [
    '.1.3.6.1.4.1.17420.1.2.9.1.19.0',
    '.1.3.6.1.4.1.17420.1.2.4.0',
];
$returned_oids = snmp_get_multi_oid($device, $oids);

$hardware = $returned_oids['.1.3.6.1.4.1.17420.1.2.9.1.19.0'];
$hardware = str_replace('"', '', $hardware);
$version = $returned_oids['.1.3.6.1.4.1.17420.1.2.4.0'];
$version = str_replace('"', '', $version);
