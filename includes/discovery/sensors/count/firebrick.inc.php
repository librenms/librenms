<?php

/*** L2TP ***/
$l2tpTable = snmpwalk_cache_multi_oid($device, 'fbL2tpPeerTable', [], 'FIREBRICK-L2TP-MIB', 'firebrick');

if ($l2tpTable) {
    $tunnels = [
        0 => ['description' => 'Free Tunnels', 'crit_low' => 10, 'warn_low' => 20, 'warn_high' => 30000, 'crit_high' => 30000],
        1 => ['description' => 'Opening Tunnels'],
        2 => ['description' => 'Live Incoming Tunnels'],
        3 => ['description' => 'Live Outgoing Tunnels'],
        4 => ['description' => 'Closing Tunnels'],
        5 => ['description' => 'Failed Tunnels'],
        6 => ['description' => 'Closed Tunnels'],
    ];
    $sessions = [
        1 => ['description' => 'Free Sessions', 'crit_low' => 10, 'warn_low' => 20, 'warn_high' => 0, 'crit_high' => 0],
        2 => ['description' => 'Waiting Sessions'],
        3 => ['description' => 'Opening Sessions'],
        4 => ['description' => 'Negotiating Sessions'],
        5 => ['description' => 'Authentication Pending Sessions'],
        6 => ['description' => 'Started Sessions'],
        7 => ['description' => 'Live Sessions'],
        8 => ['description' => 'Accounting Pending Sessions'],
        9 => ['description' => 'Closing Sessions'],
        10 => ['description' => 'Closed Sessions', 'crit_low' => 0, 'warn_low' => 0, 'warn_high' => 0, 'crit_high' => 0],
        11 => ['description' => 'Free Session Slots', 'crit_low' => 10, 'warn_low' => 20, 'warn_high' => 0, 'crit_high' => 0],
    ];

    foreach ($tunnels as $idx => $cfg) {
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.1701.0.1.' . $idx,
            'l2tp.tunnels.' . $idx,
            'firebrick',
            $cfg['description'],
            (isset($cfg['divisor']) ? $cfg['divisor'] : '1'),
            '1',
            (isset($cfg['crit_low']) ? $cfg['crit_low'] : 0),
            (isset($cfg['warn_low']) ? $cfg['warn_low'] : 0),
            (isset($cfg['warn_high']) ? $cfg['warn_high'] : 15),
            (isset($cfg['crit_high']) ? $cfg['crit_high'] : 20),
            );
    }
    foreach ($sessions as $idx => $cfg) {
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.1701.0.2.' . $idx,
            'l2tp.sessions.' . $idx,
            'firebrick',
            $cfg['description'],
            (isset($cfg['divisor']) ? $cfg['divisor'] : '1'),
            '1',
            (isset($cfg['crit_low']) ? $cfg['crit_low'] : 0),
            (isset($cfg['warn_low']) ? $cfg['warn_low'] : 0),
            (isset($cfg['warn_high']) ? $cfg['warn_high'] : 15),
            (isset($cfg['crit_high']) ? $cfg['crit_high'] : 20),
            );
    }
}

/*** VOIP ***/
$carriersTable = snmpwalk_cache_multi_oid($device, 'fbSipCarrierTable', [], 'FIREBRICK-VOIP-MIB', 'firebrick');

if ($carriersTable) {
    // It has a carriers table

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.4.1.24693.100.5060.1.0',
        '1',
        'firebrick',
        'Total active call legs',
        '1',
        '1',
        0,
        0,
        800,
        1000,
        );

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.4.1.24693.100.5060.2.0',
        '2',
        'firebrick',
        'RADIUS-based registrations',
        '1',
        '1',
        0,
        0,
        800,
        1000,
        );

    foreach ($carriersTable as $idx => $carrier) {
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.5060.3.1.2.' . $idx,
            'carrier' . $idx,
            'firebrick',
            'Total Legs: ' . $carrier['fbSipCarrierName'],
            '1',
            '1',
            0,
            0,
            800,
            1000,
            );
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.5060.3.1.3.' . $idx,
            'carrier' . $idx,
            'firebrick',
            'Connected Legs: ' . $carrier['fbSipCarrierName'],
            '1',
            '1',
            0,
            0,
            800,
            1000,
            );
    }
}
