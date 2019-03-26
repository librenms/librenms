<?php

$hardware_array = snmp_get_multi($device, ['upsIdentManufacturer.0', 'upsIdentModel.0', 'upsIdentAgentSoftwareVersion.0'], '-OQUs', 'UPS-MIB');
d_echo($hardware_array);
$hardware = trim($hardware_array[0]['upsIdentManufacturer'], '"') . ' - ' . trim($hardware_array[0]['upsIdentModel'], '"');
$version  = trim($hardware_array[0]['upsIdentAgentSoftwareVersion'], '"');
