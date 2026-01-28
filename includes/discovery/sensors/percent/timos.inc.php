<?php

/**
 * timos.inc.php
 *
 * Nokia TiMOS NAT Pool LSN Member Block Usage sensors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

// TIMETRA-NAT-MIB::tmnxNatPlLsnMemberBlockUsage
// Index: vRtrID, tmnxNatPlName (string), tmnxNatIsaMemberId

$blockUsageData = SnmpQuery::numericIndex()->hideMib()->walk('TIMETRA-NAT-MIB::tmnxNatPlLsnMemberBlockUsage')->values();

// OID base: 1.3.6.1.4.1.6527.3.1.2.65.1.4.4.1.2
// Format from numericIndex: tmnxNatPlLsnMemberBlockUsage.vRtrID.len.ascii_bytes.memberId

foreach ($blockUsageData as $oid => $value) {
    // Extract the numeric index from the OID
    if (! preg_match('/tmnxNatPlLsnMemberBlockUsage\.(.+)$/', (string) $oid, $matches)) {
        continue;
    }

    $index = $matches[1];
    $indexParts = explode('.', $index);
    if (count($indexParts) < 3) {
        continue;
    }

    // First part is vRtrID
    $vRtrId = array_shift($indexParts);

    // Second part is the string length of the pool name
    $nameLength = (int) array_shift($indexParts);

    // Next $nameLength parts are ASCII codes for the pool name
    $nameAscii = array_splice($indexParts, 0, $nameLength);
    $poolName = implode('', array_map(chr(...), $nameAscii));

    $descr = "$poolName Block Usage";

    discover_sensor(
        null,
        'percent',
        $device,
        ".1.3.6.1.4.1.6527.3.1.2.65.1.4.4.1.2.$index",
        "tmnxNatPlLsnMemberBlockUsage.$index",
        'timos',
        $descr,
        1,      // divisor
        1,      // multiplier
        null,   // low_limit
        null,   // low_warn_limit
        80,     // warn_limit
        95,     // high_limit
        $value,
        'snmp',
        null,   // entPhysicalIndex
        null,   // entPhysicalIndex_measured
        null,   // user_func
        'NAT Pool Usage',
        'GAUGE'
    );
}
