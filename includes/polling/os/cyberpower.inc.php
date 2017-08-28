<?php

$oids=array(
    '.1.3.6.1.4.1.3808.1.1.3.1.5.0',
    '.1.3.6.1.4.1.3808.1.1.3.1.3.0'
);
$returned_oids=snmp_get_multi_oid($device, $oids);

$hardware = $hardware = $returned_oids['.1.3.6.1.4.1.3808.1.1.3.1.5.0'];
$hardware = str_replace('"', '', $hardware);
$version = $hardware = $returned_oids['.1.3.6.1.4.1.3808.1.1.3.1.3.0'];
$version = str_replace('"', '', $version);
