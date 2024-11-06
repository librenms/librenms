<?php
echo 'linuxprocesses ';

$prTable = snmpwalk_cache_oid($device, 'prTable', [], 'UCD-SNMP-MIB');
foreach ($prTable as $entry) {
    $descr = "Proc count: {$entry['prNames']}";
    $state_name = "prCount";
    $low_limit = $entry['prMin'] - 1;
    $high_limit = $entry['prMax'] + 1;

    discover_sensor(
        unused: null,
        class: 'count',
        device: $device,
        oid: ".1.3.6.1.4.1.2021.2.1.5." . $entry['prIndex'],
        index: $entry['prNames'],
        type: $state_name,
        descr: $descr,
        divisor: 1,
        multiplier: 1,
        low_limit: $low_limit,
        low_warn_limit: null,
        warn_limit: null,
        high_limit: $high_limit,
        current: $entry['prCount'],
        poller_type: 'snmp',
        user_func: null,
        group: "Processes",
        rrd_type: 'GAUGE'
    );
}
