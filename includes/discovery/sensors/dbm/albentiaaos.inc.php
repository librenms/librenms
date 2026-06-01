<?php

$radio = SnmpQuery::cache()
    ->walk('ALBENTIA-AS-MIB::radioInfoTable')
    ->table(1);

foreach ($radio as $idx => $row) {
    $rssi = $row['ALBENTIA-AS-MIB::radioInfoTargetRSSI'] ?? null;
    if ($rssi === null || $rssi === '') {
        continue;
    }

    discover_sensor(
        null, 'dbm', $device,
        '.1.3.6.1.4.1.28087.12.10.10.5.1.7.' . $os->encodeStringIndex((string) $idx),
        'radioInfoTargetRSSI',
        'albentiaaos',
        'Target RSSI',
        1, 1, null, null, null, null,
        (int) $rssi
    );
    break;
}

unset($radio, $idx, $row, $rssi);
