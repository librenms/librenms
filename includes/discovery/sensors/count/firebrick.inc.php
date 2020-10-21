<?php
/*** L2TP ***/
$l2tpTable = snmpwalk_cache_multi_oid($device, 'fbL2tpPeerTable', [], 'FIREBRICK-L2TP-MIB', 'firebrick');

if($l2tpTable){

    $tunnels = array(
        0 => array("description" => "Free Tunnels"),
        1 => array("description" => "Opening Tunnels"),
        2 => array("description" => "Live Incoming Tunnels"),
        3 => array("description" => "Live Outgoing Tunnels"),
        4 => array("description" => "Closing Tunnels"),
        5 => array("description" => "Failed Tunnels"),
        6 => array("description" => "Closed Tunnels"),
    );
    $sessions = array(
        1 => array("description" => "Free Sessions"),
        2 => array("description" => "Waiting Sessions"),
        3 => array("description" => "Opening Sessions"),
        4 => array("description" => "Negotiating Sessions"),
        5 => array("description" => "Authentication Pending Sessions"),
        6 => array("description" => "Started Sessions"),
        7 => array("description" => "Live Sessions"),
        8 => array("description" => "Accounting Pending Sessions"),
        9 => array("description" => "Closing Sessions"),
        10 => array("description" => "Closed Sessions"),
        11 => array("description" => "Free Session Slots"),
    );

    foreach($tunnels as $idx => $cfg){
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            ".1.3.6.1.4.1.24693.100.1701.0.1." . $idx,
            "l2tp.tunnels." . $idx,
            'firebrick',
            $cfg["description"],
            (isset($cfg["divisor"]) ? $cfg["divisor"] : '1'),
            '1',
            (isset($cfg["crit_low"]) ? $cfg["crit_low"] : 0),
            (isset($cfg["warn_low"]) ? $cfg["warn_low"] : 0),
            (isset($cfg["warn_high"]) ? $cfg["warn_high"] : 15),
            (isset($cfg["crit_high"]) ? $cfg["crit_high"] : 20),
            );
    }
    foreach($sessions as $idx => $cfg){
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            ".1.3.6.1.4.1.24693.100.1701.0.2." . $idx,
            "l2tp.sessions." . $idx,
            'firebrick',
            $cfg["description"],
            (isset($cfg["divisor"]) ? $cfg["divisor"] : '1'),
            '1',
            (isset($cfg["crit_low"]) ? $cfg["crit_low"] : 0),
            (isset($cfg["warn_low"]) ? $cfg["warn_low"] : 0),
            (isset($cfg["warn_high"]) ? $cfg["warn_high"] : 15),
            (isset($cfg["crit_high"]) ? $cfg["crit_high"] : 20),
            );
    }

}

/*** VOIP ***/
$carriersTable = snmpwalk_cache_multi_oid($device, 'fbSipCarrierTable', [], 'FIREBRICK-VOIP-MIB', 'firebrick');

if($carriersTable){
    // It has a carriers table

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        ".1.3.6.1.4.1.24693.100.5060.1.0",
        "1",
        'firebrick',
        "Total active call legs",
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
        ".1.3.6.1.4.1.24693.100.5060.2.0",
        "2",
        'firebrick',
        "RADIUS-based registrations",
        '1',
        '1',
        0,
        0,
        800,
        1000,
        );

    foreach($carriersTable as $idx => $carrier){
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            ".1.3.6.1.4.1.24693.100.5060.3.1.2." . $idx,
            "carrier" . $idx,
            'firebrick',
            "Total Legs: " . $carrier["fbSipCarrierName"],
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
            ".1.3.6.1.4.1.24693.100.5060.3.1.3." . $idx,
            "carrier" . $idx,
            'firebrick',
            "Connected Legs: " . $carrier["fbSipCarrierName"],
            '1',
            '1',
            0,
            0,
            800,
            1000,
            );
    }

}