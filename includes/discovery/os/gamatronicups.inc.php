<?php

if (empty($sysDescr)) {
    if (snmp_get($device, 'psUnitManufacture.0', '-Oqv', 'GAMATRONIC-MIB', 'gamtronic') == 'Gamatronic') {
        $os = 'gamatronicups';
    }
}
