<?php

$hardware = snmp_get($device, 'deviceDescr.0', '-Ovq', 'IOMEGANAS-MIB');
