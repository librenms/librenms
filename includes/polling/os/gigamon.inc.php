<?php

// Gigamon-GigaVUE
$gigamon_hardware = snmp_get_multi($device, ['manufacturer.0', 'model.0', 'version.0', 'serialNumber.0'], '-OQUs', 'GIGAMON-SNMP-MIB');

#$hardware = $gigamon_hardware[0]['model'];
$version = $gigamon_hardware[0]['version'];
$serial = $gigamon_hardware[0]['serialNumber'];
#$manufacturer = $gigamon_hardware[0]['manufacturer']
