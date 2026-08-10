<?php

/**
 * timos.inc.php
 *
 * Nokia TiMOS NAT ISA Member Resource sensors
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

// TIMETRA-NAT-MIB::tmnxNatIsaMemberResrcTable
// Index: tmnxNatIsaGrpId, tmnxNatIsaMemberId, tmnxNatIsaMemberResrcId
// This table provides dynamic resource names and values for NAT ISA members

$resrcData = SnmpQuery::hideMib()->walk([
    'TIMETRA-NAT-MIB::tmnxNatIsaMemberResrcName',
    'TIMETRA-NAT-MIB::tmnxNatIsaMemberResrcVal',
])->table(3);

// OID base: 1.3.6.1.4.1.6527.3.1.2.65.1.1.3.6.1
// .2 = tmnxNatIsaMemberResrcName
// .4 = tmnxNatIsaMemberResrcVal

// Resource names to create sensors for (case-insensitive match)
// Ugly, but so is the MIB here...
$allowedResources = [
    'large-scale hosts',
    'port-ranges configured',
    'port-ranges used',
];

if (! empty($resrcData)) {
    foreach ($resrcData as $grpId => $grpData) {
        foreach ($grpData as $memberId => $memberData) {
            foreach ($memberData as $resrcId => $entry) {
                if (isset($entry['tmnxNatIsaMemberResrcName'], $entry['tmnxNatIsaMemberResrcVal'])) {
                    $name = $entry['tmnxNatIsaMemberResrcName'];

                    // Only create sensors for allowed resource types
                    $matched = false;
                    foreach ($allowedResources as $allowed) {
                        if (stripos($name, $allowed) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                    if (! $matched) {
                        continue;
                    }

                    $value = $entry['tmnxNatIsaMemberResrcVal'];
                    $index = "$grpId.$memberId.$resrcId";

                    // Build human-readable description
                    $descr = "NAT ISA $grpId.$memberId $name";

                    // OID for tmnxNatIsaMemberResrcVal (tmnxNatObjs.tmnxNatIsaObjs.tmnxNatIsaMdaStatObjs.tmnxNatIsaMemberResrcTable.tmnxNatIsaMemberResrcEntry.tmnxNatIsaMemberResrcVal)
                    // Full path: enterprises.6527.3.1.2.65.1.1.3.6.1.4
                    $oid = ".1.3.6.1.4.1.6527.3.1.2.65.1.1.3.6.1.4.$index";

                    discover_sensor(
                        null,
                        'count',
                        $device,
                        $oid,
                        "tmnxNatIsaMemberResrcVal.$index",
                        'timos',
                        $descr,
                        1,      // divisor
                        1,      // multiplier
                        null,   // low_limit
                        null,   // low_warn_limit
                        null,   // warn_limit
                        null,   // high_limit
                        $value,
                        'snmp',
                        null,   // entPhysicalIndex
                        null,   // entPhysicalIndex_measured
                        null,   // user_func
                        'NAT ISA Resources',
                        'GAUGE'
                    );
                }
            }
        }
    }
}

// TIMETRA-NAT-MIB::tmnxNatVappPlcyStatsTable
// Index: tmnxNatVappPlcyName (string), unknown1, unknown2, tmnxNatVappPlcyStatsType (string)
// This table provides per-policy NAT usage statistics

$vappStatsData = SnmpQuery::numericIndex()->hideMib()->walk('TIMETRA-NAT-MIB::tmnxNatVappPlcyStatsVal')->values();

// OID base: 1.3.6.1.4.1.6527.3.1.2.65.1.1.4.5.1.3
// Index format: <len>.<ASCII policy name>.<idx1>.<idx2>.<stat_type_enum>
// Stats we care about: hostsActive (1), hostsPeak (2)

$allowedVappStats = [
    1 => 'Hosts Active',
    2 => 'Hosts Peak',
];

foreach ($vappStatsData as $oid => $value) {
    // Extract the numeric index from the OID
    if (! preg_match('/tmnxNatVappPlcyStatsVal\.(.+)$/', (string) $oid, $matches)) {
        continue;
    }

    $index = $matches[1];
    $indexParts = explode('.', $index);
    if (count($indexParts) < 4) {
        continue;
    }

    // First part is the string length of the policy name
    $nameLength = (int) array_shift($indexParts);
    // After the name, we expect 3 more parts: idx1, idx2, stat_type_enum
    if ($nameLength < 1 || $nameLength > count($indexParts) - 3) {
        continue;
    }

    // Next $nameLength parts are ASCII codes for the policy name
    $nameAscii = array_splice($indexParts, 0, $nameLength);
    $policyName = implode('', array_map(chr(...), $nameAscii));

    // Remaining parts: idx1, idx2, stat_type_enum
    if (count($indexParts) < 3) {
        continue;
    }

    $idx1 = array_shift($indexParts);
    $idx2 = array_shift($indexParts);
    $statType = (int) array_shift($indexParts);

    // Only create sensors for allowed stat types
    if (! isset($allowedVappStats[$statType])) {
        continue;
    }

    $statName = $allowedVappStats[$statType];
    $descr = "$policyName $statName";

    // OID for tmnxNatVappPlcyStatsVal
    // Full path: enterprises.6527.3.1.2.65.1.1.4.5.1.3
    $oid = ".1.3.6.1.4.1.6527.3.1.2.65.1.1.4.5.1.3.$index";

    discover_sensor(
        null,
        'count',
        $device,
        $oid,
        "tmnxNatVappPlcyStatsVal.$index",
        'timos',
        $descr,
        1,      // divisor
        1,      // multiplier
        null,   // low_limit
        null,   // low_warn_limit
        null,   // warn_limit
        null,   // high_limit
        $value,
        'snmp',
        null,   // entPhysicalIndex
        null,   // entPhysicalIndex_measured
        null,   // user_func
        'NAT Policy Stats',
        'GAUGE'
    );
}
