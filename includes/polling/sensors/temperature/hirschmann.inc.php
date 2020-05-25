<?php

if ($device['os'] == 'hirschmann') {
    echo 'Hirschmann Device: ';

    $sensor_value = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmTemperature.0', '-Oqv');
}
