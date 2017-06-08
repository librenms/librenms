<?php

$hardware = 'Ubiquiti '.trim(snmp_get($device, 'dot11manufacturerProductName.10', '-Ovq', 'IEEE802dot11-MIB'));

$version         = trim(snmp_get($device, 'dot11manufacturerProductVersion.10', '-Ovq', 'IEEE802dot11-MIB'));
list(, $version) = preg_split('/\.v/', $version);

// EOF
