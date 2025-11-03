<?php

use LibreNMS\Util\Number;

echo 'RFC1628 ';

$ups_alarms_present = snmp_get($device, 'upsAlarmsPresent.0', '-OqvU', 'UPS-MIB');
if (is_numeric($ups_alarms_present)) {
    $ups_alarms_present_oid = '.1.3.6.1.2.1.33.1.6.1.0';

    discover_sensor(
        null,
        'count',
        $device,
        $ups_alarms_present_oid,
        '0',
        'rfc1628',
        'UPS Alarms',
        1,
        1,
        null,
        null,
        null,
        null,
        $ups_alarms_present
    );
}
