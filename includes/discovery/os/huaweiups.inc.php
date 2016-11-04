<?php

if (starts_with($sysDescr, 'Linux GSE200M')) {
    if (str_contains(snmp_get($device, 'UPS-MIB::upsIdentManufacturer.0', '-Oqv', ''), 'HUAWEI')) {
        $os = 'huaweiups';
    }
}
