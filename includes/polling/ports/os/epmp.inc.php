<?php

$link_status = \SnmpQuery::get([
    'CAMBIUM-PMP80211-MIB::cambiumLANModeStatus.0',
    'CAMBIUM-PMP80211-MIB::cambiumLANSpeedStatus.0',
    'CAMBIUM-PMP80211-MIB::cambiumLAN2ModeStatus.0',
    'CAMBIUM-PMP80211-MIB::cambiumLAN2SpeedStatus.0',
])->values();

$lan_status_map = [
    'LAN interface 1' => [
        'mode' => 'CAMBIUM-PMP80211-MIB::cambiumLANModeStatus.0',
        'speed' => 'CAMBIUM-PMP80211-MIB::cambiumLANSpeedStatus.0',
    ],
    'LAN interface 2' => [
        'mode' => 'CAMBIUM-PMP80211-MIB::cambiumLAN2ModeStatus.0',
        'speed' => 'CAMBIUM-PMP80211-MIB::cambiumLAN2SpeedStatus.0',
    ],
];

foreach ($port_stats as $index => $epmp_port) {
    $if_descr = $epmp_port['ifDescr'] ?? null;
    if ($if_descr && isset($lan_status_map[$if_descr])) {
        $mode_key = $lan_status_map[$if_descr]['mode'];
        $speed_key = $lan_status_map[$if_descr]['speed'];
        $mode = $link_status[$mode_key] ?? null;
        $speed = $link_status[$speed_key] ?? null;

        if (is_numeric($speed)) {
            $speed = (int) $speed;
            $port_stats[$index]['ifHighSpeed'] = $speed > 0 ? $speed : 0;
        }

        if (is_numeric($mode)) {
            $mode = (int) $mode;
            $port_stats[$index]['ifDuplex'] = match ($mode) {
                0 => 'halfDuplex',
                1 => 'fullDuplex',
                default => null,
            };
        }

        continue;
    }

    // ePMP WLAN ports report ifHighSpeed in bps instead of the RFC2233 Mbps gauge.
    // Normalize the value before the generic port poller multiplies it.
    $raw_speed = $epmp_port['ifHighSpeed'] ?? null;
    if (! is_numeric($raw_speed)) {
        continue;
    }

    $raw_speed = (int) $raw_speed;
    if ($raw_speed === 0) {
        continue;
    }

    if ($raw_speed >= 4290000000 && ($epmp_port['ifOperStatus'] ?? null) !== 'up') {
        $port_stats[$index]['ifHighSpeed'] = 0;

        continue;
    }

    if ($raw_speed > 1000000) {
        $port_stats[$index]['ifHighSpeed'] = (int) round($raw_speed / 1000000);
    }
}
