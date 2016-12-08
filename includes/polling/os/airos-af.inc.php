<?php

$hardware = 'Ubiquiti AF '.trim(snmp_get($device, 'dot11manufacturerProductName.5', '-Ovq', 'IEEE802dot11-MIB'));

$version         = trim(snmp_get($device, 'dot11manufacturerProductVersion.5', '-Ovq', 'IEEE802dot11-MIB'));
list(, $version) = preg_split('/\.v/', $version);

// EOF
