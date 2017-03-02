<?php

$OIDs=array(
    '.1.3.6.1.4.1.3808.1.1.3.1.5.0',
    '.1.3.6.1.4.1.3808.1.1.3.1.3.0'
);
$returnedOIDs=snmp_get_multi_oid($device, $OIDs);

$hardware = $hardware = $returnedOIDs['.1.3.6.1.4.1.3808.1.1.3.1.5.0'];
$hardware = str_replace('"', '', $hardware);
$version = $hardware = $returnedOIDs['.1.3.6.1.4.1.3808.1.1.3.1.3.0'];
$version = str_replace('"', '', $version);
