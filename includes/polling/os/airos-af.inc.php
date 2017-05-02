<?php

$hardware = 'Ubiquiti AF '.trim(snmp_get($device, 'dot11manufacturerProductName.5', '-Ovq', 'IEEE802dot11-MIB'));

$version  = snmp_get($device, 'fwVersion.1', '-Ovq', 'UBNT-AirFIBER-MIB');
