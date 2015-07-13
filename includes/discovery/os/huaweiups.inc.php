<?php

if (!$os) {
    if (preg_match('/^Linux GSE200M/', $sysDescr)) {
        if (strstr(snmp_get($device, 'UPS-MIB::upsIdentManufacturer.0', '-Oqv', ''), 'HUAWEI')) {
            $os = 'huaweiups';
        }
    }
}
