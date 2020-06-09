<?php

use LibreNMS\RRD\RrdDefinition;

$oids = array('spsProductModel.0', 'spsSystemSerial.0', 'spsSystemVersion.0');
$vxoa = snmp_get_multi($device, $oids, '-OQUs', 'SILVERPEAK-MGMT-MIB');

$hardware = $vxoa[0]['spsProductModel'];
$serial = $vxoa[0]['spsSystemSerial'];
$version = $vxoa[0]['spsSystemVersion'];
