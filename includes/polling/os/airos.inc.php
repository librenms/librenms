<?php

$result = snmp_getnext($device, "dot11manufacturerProductName", '-OQv', 'IEEE802dot11-MIB');
$hardware = 'Ubiquiti ' . $result;

$result = snmp_getnext($device, "dot11manufacturerProductVersion", '-OQv', 'IEEE802dot11-MIB');
$version = $result;
list(, $version) = preg_split('/\.v/', $version);

unset($result);

// EOF
