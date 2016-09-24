<?php

if (empty($sysDescr)) {
    if (snmp_get($device, 'GAMATRONIC-MIB::psUnitManufacture.0', '-Oqv', '') == 'Gamatronic') {
        $os = 'gamatronicups';
    }
}
