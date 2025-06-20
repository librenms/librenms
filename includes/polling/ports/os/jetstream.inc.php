<?php

/**
 * jetstream.inc.php
 *
 * LibreNMS Jetstream port rewite rule
 *
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// older TP-LINK have port names like:
// ifName.49176 = gigabitEthernet 1/0/24 : copper
// ifName.49177 = gigabitEthernet 1/0/25 : fiber
//
// but will send LLDP info as:
// GigabitEthernet1/0/24
// FiberEthernet1/0/25
// so make port name equal to LLDP port name

foreach ($port_stats as $index => $port_data) {
    if (preg_match('/gigabitethernet\s(\d+\/\d+\/\d+)\s\:\s(fiber|copper)/i', $port_data['ifName'] ?? '', $jsArray)) {
        $base = 'Ethernet' . $jsArray[1];
        $name = ($jsArray[2] == 'fiber') ? 'Fiber' . $base : 'Gigabit' . $base;
        $port_stats[$index]['ifName'] = $port_stats[$index]['ifDescr'] = $name;
    }
}
