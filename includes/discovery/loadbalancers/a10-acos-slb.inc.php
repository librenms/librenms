<?php

/*
 * LibreNMS module to capture A10 ACOS SLB (Server Load Balancing) details
 *
 * Uses LibreNMS's "components" based Load Balancer architecture to model:
 *
 *   acos-slb-vs       Virtual Server (VIP)
 *   acos-slb-vport    Virtual Server Port (listener)
 *   acos-slb-pool     Service Group
 *   acos-slb-member   Service Group Member
 *   acos-slb-server   Real Server (node)
 *
 * OIDs come from A10-AX-MIB (axApp = enterprises.22610.2.4.3):
 *   axServers(2) / axServiceGroups(3) / axVirtualServers(4)
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Base of the A10 axApp tree (enterprises.22610.2.4.3)
$acosAppBase = '1.3.6.1.4.1.22610.2.4.3';

// Walk a numeric OID and return [oid-without-leading-dot => value],
// or null if the agent did not answer (as opposed to an empty table)
if (! function_exists('acos_slb_walk_numeric')) {
    function acos_slb_walk_numeric(string $oid): ?array
    {
        $response = SnmpQuery::numeric()->walk($oid);
        if ($response->hasConnectionError()) {
            return null;
        }

        $result = [];
        foreach ($response->values() as $key => $value) {
            $result[ltrim((string) $key, '.')] = $value;
        }

        return $result;
    }
}

if (! function_exists('acos_slb_walk_table')) {
    /*
     * Walk a SNMP table where every needed column lives under the same
     * "<base>.<column>" OID and rows are identified by whatever numeric index
     * suffix SNMP assigns (string index, possibly multi-part - we never need to
     * decode it, only use it as an opaque per-row key).
     *
     * $columns is ['fieldName' => columnNumber, ...] and must include the column
     * used to discover which rows exist ($idField, usually the row's name).
     *
     * Returns null if any walk failed, so callers can tell an incomplete read
     * apart from a table that is genuinely empty.
     */
    function acos_slb_walk_table($device, $base, array $columns, $idField)
    {
        $idOid = $base . '.' . $columns[$idField];
        $idValues = acos_slb_walk_numeric($idOid);

        if ($idValues === null) {
            return null;
        }

        $rows = [];
        $prefix = $idOid . '.';
        foreach ($idValues as $oid => $value) {
            $oid = ltrim((string) $oid, '.');
            if (! str_contains($oid, $prefix)) {
                continue;
            }
            [, $suffix] = explode($prefix, $oid, 2);
            $rows[$suffix] = [$idField => $value];
        }

        foreach ($columns as $field => $col) {
            if ($field === $idField) {
                continue;
            }
            $colOid = $base . '.' . $col;
            $values = acos_slb_walk_numeric($colOid);
            if ($values === null) {
                return null;
            }
            foreach ($rows as $suffix => &$row) {
                $row[$field] = $values[$colOid . '.' . $suffix] ?? null;
            }
            unset($row);
        }

        return $rows;
    }
}

// ---------------------------------------------------------------------
// axVirtualServerStatTable - VIP level aggregate (address indexed)
// ---------------------------------------------------------------------
$acosVsStat = acos_slb_walk_table($device, $acosAppBase . '.4.2.1.1', [
    'address' => 1,
    'name' => 2,
    'pktsin' => 3,
    'bytesin' => 4,
    'pktsout' => 5,
    'bytesout' => 6,
    'persistconns' => 7,
    'totconns' => 8,
    'curconns' => 9,
    'status' => 10,
    'displaystatus' => 11,
    'l7reqs' => 12,
    'l7currreqs' => 13,
    'l7succreqs' => 14,
    'peakconns' => 15,
], 'address');

// ---------------------------------------------------------------------
// axVirtualServerPortStatTable - listener level (address+type+port indexed)
// ---------------------------------------------------------------------
$acosVportStat = acos_slb_walk_table($device, $acosAppBase . '.4.4.1.1', [
    'address' => 1,
    'porttype' => 2,
    'portnum' => 3,
    'name' => 4,
    'status' => 5,
    'pktsin' => 6,
    'bytesin' => 7,
    'pktsout' => 8,
    'bytesout' => 9,
    'persistconns' => 10,
    'totconns' => 11,
    'curconns' => 12,
    'displaystatus' => 13,
    'l7reqs' => 14,
    'l7currreqs' => 15,
    'l7succreqs' => 16,
    'peakconns' => 17,
], 'address');

// axVirtualServerPortTable (config) - only source of the service-group binding
$acosVportCfg = acos_slb_walk_table($device, $acosAppBase . '.4.3.1.1', [
    'name' => 1,
    'porttype' => 2,
    'portnum' => 3,
    'enabled' => 5,
    'servicegroup' => 6,
], 'name');
// Index config rows by "name|porttype|portnum" so we can bind them to the
// (differently indexed) stat rows using their actual values, not their OIDs.
$acosVportCfgByKey = [];
foreach ($acosVportCfg ?? [] as $row) {
    $key = ($row['name'] ?? '') . '|' . ($row['porttype'] ?? '') . '|' . ($row['portnum'] ?? '');
    $acosVportCfgByKey[$key] = $row;
}

// ---------------------------------------------------------------------
// axServiceGroupStatTable - pool level (name indexed)
// ---------------------------------------------------------------------
$acosPoolStat = acos_slb_walk_table($device, $acosAppBase . '.3.2.1.1', [
    'name' => 1,
    'pktsin' => 2,
    'bytesin' => 3,
    'pktsout' => 4,
    'bytesout' => 5,
    'totconns' => 6,
    'curconns' => 7,
    'persistconns' => 8,
    'displaystatus' => 9,
    'l7reqs' => 10,
    'l7currreqs' => 11,
    'l7succreqs' => 12,
    'peakconns' => 13,
], 'name');

// axServiceGroupTable (config) - name indexed, same index space as the stat
// table above, so the numeric suffix matches directly.
$acosPoolCfg = acos_slb_walk_table($device, $acosAppBase . '.3.1.2.1', [
    'name' => 1,
    'grouptype' => 2,
    'lbalgorithm' => 3,
], 'name');

// ---------------------------------------------------------------------
// axServiceGroupMemberStatTable - pool member level
// (group name + addr type + server name + port, name indexed like the pool)
// ---------------------------------------------------------------------
$acosMemberStat = acos_slb_walk_table($device, $acosAppBase . '.3.4.1.1', [
    'poolname' => 1,
    'addrtype' => 2,
    'servername' => 3,
    'portnum' => 4,
    'pktsin' => 5,
    'bytesin' => 6,
    'pktsout' => 7,
    'bytesout' => 8,
    'persistconns' => 9,
    'totconns' => 10,
    'curconns' => 11,
    'status' => 12,
    'l7reqs' => 13,
    'l7currreqs' => 14,
    'l7succreqs' => 15,
    'responsetime' => 16,
    'peakconns' => 17,
], 'poolname');

// axServiceGroupMemberTable (config) - only source of member priority
$acosMemberCfg = acos_slb_walk_table($device, $acosAppBase . '.3.3.1.1', [
    'poolname' => 1,
    'addrtype' => 2,
    'servername' => 3,
    'portnum' => 4,
    'priority' => 5,
], 'poolname');
$acosMemberCfgByKey = [];
foreach ($acosMemberCfg ?? [] as $row) {
    $key = ($row['poolname'] ?? '') . '|' . ($row['addrtype'] ?? '') . '|' . ($row['servername'] ?? '') . '|' . ($row['portnum'] ?? '');
    $acosMemberCfgByKey[$key] = $row;
}

// ---------------------------------------------------------------------
// axServerStatTable - real server / node level (address indexed)
// ---------------------------------------------------------------------
$acosServerStat = acos_slb_walk_table($device, $acosAppBase . '.2.2.2.1', [
    'address' => 1,
    'name' => 2,
    'pktsin' => 3,
    'bytesin' => 4,
    'pktsout' => 5,
    'bytesout' => 6,
    'totconns' => 7,
    'curconns' => 8,
    'persistconns' => 9,
    'status' => 10,
    'l7reqs' => 11,
    'l7currreqs' => 12,
    'l7succreqs' => 13,
    'peakconns' => 14,
], 'address');

// axServerTable (config) - name indexed, bind to stat rows by server name
$acosServerCfg = acos_slb_walk_table($device, $acosAppBase . '.2.1.2.1', [
    'name' => 1,
    'address' => 2,
    'enabledstate' => 3,
    'healthmonitor' => 4,
    'monitorstate' => 5,
    'weight' => 7,
], 'name');
$acosServerCfgByName = [];
foreach ($acosServerCfg ?? [] as $row) {
    $acosServerCfgByName[$row['name']] = $row;
}

// Bridge tables that are name-indexed on one side but address-indexed on the
// other: build name => config-row lookups so we can merge them onto the
// (address indexed) stat-table driven components below.
$acosPoolCfgByName = [];
foreach ($acosPoolCfg ?? [] as $row) {
    $acosPoolCfgByName[$row['name']] = $row;
}

// A failed config walk would wipe bindings (service group, priority, ...), so
// treat the matching stat table as unread too. Components of an unread table
// are left untouched rather than updated or deleted.
if ($acosVportCfg === null) {
    $acosVportStat = null;
}
if ($acosPoolCfg === null) {
    $acosPoolStat = null;
}
if ($acosMemberCfg === null) {
    $acosMemberStat = null;
}
if ($acosServerCfg === null) {
    $acosServerStat = null;
}

$acosReadTypes = array_keys(array_filter([
    'acos-slb-vs' => $acosVsStat !== null,
    'acos-slb-vport' => $acosVportStat !== null,
    'acos-slb-pool' => $acosPoolStat !== null,
    'acos-slb-member' => $acosMemberStat !== null,
    'acos-slb-server' => $acosServerStat !== null,
]));

// Reconcile whenever at least one table was read, even if it is empty, so
// removed SLB objects are cleared. Skip entirely when every walk failed.
if (! empty($acosReadTypes)) {
    $tblAcos = [];

    // ---- Virtual Servers (VIPs) ----
    foreach ($acosVsStat ?? [] as $uid => $row) {
        $result = [
            'type' => 'acos-slb-vs',
            'UID' => (string) $uid,
            'label' => $row['name'],
            'hash' => hash('crc32', (string) $uid),
            'address' => $row['address'],
            'displaystatus' => $row['displaystatus'],
        ];

        // axVirtualServerStatStatus: 1=up 2=down 3=disabled
        if ($row['status'] === null) {
            // no value for this row: unknown, leave any existing status untouched
        } elseif ($row['status'] == 2) {
            $result['status'] = 2;
            $result['error'] = 'Virtual Server is Down';
        } elseif ($row['status'] == 3) {
            $result['status'] = 1;
            $result['error'] = 'Virtual Server is Disabled';
        } elseif ($row['displaystatus'] == 3) {
            $result['status'] = 1;
            $result['error'] = 'Virtual Server is partially up';
        } else {
            $result['status'] = 0;
            $result['error'] = '';
        }

        $tblAcos[] = $result;
    }

    // ---- Virtual Server Ports (listeners) ----
    foreach ($acosVportStat ?? [] as $uid => $row) {
        $key = ($row['name'] ?? '') . '|' . ($row['porttype'] ?? '') . '|' . ($row['portnum'] ?? '');
        $cfg = $acosVportCfgByKey[$key] ?? [];

        $result = [
            'type' => 'acos-slb-vport',
            'UID' => (string) $uid,
            'label' => $row['name'] . ':' . $row['portnum'],
            'hash' => hash('crc32', (string) $uid),
            'vsname' => $row['name'],
            'address' => $row['address'],
            'port' => $row['portnum'],
            'porttype' => $row['porttype'],
            'servicegroup' => $cfg['servicegroup'] ?? null,
            'enabled' => $cfg['enabled'] ?? null,
        ];

        // axVirtualServerStatPortStatus: 1=up 2=down 3=disabled
        if ($row['status'] === null) {
            // no value for this row: unknown, leave any existing status untouched
        } elseif ($row['status'] == 2) {
            $result['status'] = 2;
            $result['error'] = 'Virtual Server Port is Down';
        } elseif ($row['status'] == 3) {
            $result['status'] = 1;
            $result['error'] = 'Virtual Server Port is Disabled';
        } else {
            $result['status'] = 0;
            $result['error'] = '';
        }

        $tblAcos[] = $result;
    }

    // ---- Service Groups (Pools) ----
    foreach ($acosPoolStat ?? [] as $uid => $row) {
        $cfg = $acosPoolCfgByName[$row['name']] ?? [];

        $result = [
            'type' => 'acos-slb-pool',
            'UID' => (string) $uid,
            'label' => $row['name'],
            'hash' => hash('crc32', (string) $uid),
            'grouptype' => $cfg['grouptype'] ?? null,
            'lbalgorithm' => $cfg['lbalgorithm'] ?? null,
        ];

        // axServiceGroupStatDisplayStatus: 1=AllUp 2=FunctionalUp 3=PartialUp 4=Stopped
        if ($row['displaystatus'] === null) {
            // no value for this row: unknown, leave any existing status untouched
        } elseif ($row['displaystatus'] == 4) {
            $result['status'] = 2;
            $result['error'] = 'Service Group is Stopped (no members up)';
        } elseif ($row['displaystatus'] == 3) {
            $result['status'] = 1;
            $result['error'] = 'Service Group is only Partially Up';
        } else {
            $result['status'] = 0;
            $result['error'] = '';
        }

        $tblAcos[] = $result;
    }

    // ---- Service Group Members (Pool Members) ----
    foreach ($acosMemberStat ?? [] as $uid => $row) {
        $key = ($row['poolname'] ?? '') . '|' . ($row['addrtype'] ?? '') . '|' . ($row['servername'] ?? '') . '|' . ($row['portnum'] ?? '');
        $cfg = $acosMemberCfgByKey[$key] ?? [];

        $result = [
            'type' => 'acos-slb-member',
            'UID' => (string) $uid,
            'label' => $row['servername'] . ':' . $row['portnum'],
            'hash' => hash('crc32', (string) $uid),
            'poolname' => $row['poolname'],
            'servername' => $row['servername'],
            'port' => $row['portnum'],
            'priority' => $cfg['priority'] ?? null,
        ];

        // axServerPortStatusInServiceGroupMemberStat: 0=disabled 1=up 2=down
        if ($row['status'] === null) {
            // no value for this row: unknown, leave any existing status untouched
        } elseif ($row['status'] == 2) {
            $result['status'] = 2;
            $result['error'] = 'Pool Member is Down';
        } elseif ($row['status'] == 0) {
            $result['status'] = 1;
            $result['error'] = 'Pool Member is Disabled';
        } else {
            $result['status'] = 0;
            $result['error'] = '';
        }

        $tblAcos[] = $result;
    }

    // ---- Real Servers (nodes) ----
    foreach ($acosServerStat ?? [] as $uid => $row) {
        $cfg = $acosServerCfgByName[$row['name']] ?? [];

        $result = [
            'type' => 'acos-slb-server',
            'UID' => (string) $uid,
            'label' => $row['name'],
            'hash' => hash('crc32', (string) $uid),
            'address' => $row['address'],
            'enabledstate' => $cfg['enabledstate'] ?? null,
            'healthmonitor' => $cfg['healthmonitor'] ?? null,
            'weight' => $cfg['weight'] ?? null,
        ];

        // axServerStatServerStatus / axServerMonitorState: 0=disabled 1=up 2=down
        if ($row['status'] === null) {
            // no value for this row: unknown, leave any existing status untouched
        } elseif ($row['status'] == 2) {
            $result['status'] = 2;
            $result['error'] = 'Real Server is Down';
        } elseif ($row['status'] == 0) {
            $result['status'] = 1;
            $result['error'] = 'Real Server is Disabled';
        } else {
            $result['status'] = 0;
            $result['error'] = '';
        }

        $tblAcos[] = $result;
    }

    // ---------------------------------------------------------------
    // Reconcile discovered objects ($tblAcos) with existing components.
    //
    // A10 devices can easily have thousands of VS/pool/member components,
    // so this diffs via a
    // type|UID keyed hash map (O(n) instead of O(n^2)). Missing 'UID'/'type'
    // on a pre-existing component (e.g. left over from an interrupted run)
    // is treated as "never matches" rather than a hard error.
    // ---------------------------------------------------------------
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id']);
    $components = $components[$device['device_id']] ?? [];

    $types = ['acos-slb-vs', 'acos-slb-vport', 'acos-slb-pool', 'acos-slb-member', 'acos-slb-server'];
    $keep = [];
    $existingByKey = [];
    foreach ($components as $k => $v) {
        if (in_array($v['type'] ?? null, $types)) {
            $keep[$k] = $v;
            $existingByKey[($v['type'] ?? '') . '|' . ($v['UID'] ?? '')] = $k;
        }
    }
    $components = $keep;

    $discoveredKeys = [];
    foreach ($tblAcos as $array) {
        $lookupKey = $array['type'] . '|' . $array['UID'];
        $discoveredKeys[$lookupKey] = true;
        $component_key = $existingByKey[$lookupKey] ?? false;

        if ($component_key === false) {
            $new_component = $component->createComponent($device['device_id'], $array['type']);
            $component_key = key($new_component);
            $components[$component_key] = array_merge($new_component[$component_key], $array);
            $existingByKey[$lookupKey] = $component_key;
            echo '+';
        } else {
            $components[$component_key] = array_merge($components[$component_key], $array);
            echo '.';
        }
    }

    foreach ($components as $key => $array) {
        $lookupKey = ($array['type'] ?? '') . '|' . ($array['UID'] ?? '');
        if (in_array($array['type'] ?? '', $acosReadTypes, true) && ! isset($discoveredKeys[$lookupKey])) {
            echo '-';
            $component->deleteComponent($key);
            unset($components[$key]);
        }
    }

    $component->setComponentPrefs($device['device_id'], $components);
    echo "\n";
}

unset(
    $acosAppBase,
    $acosReadTypes,
    $acosVsStat,
    $acosVportStat,
    $acosVportCfg,
    $acosVportCfgByKey,
    $acosPoolStat,
    $acosPoolCfg,
    $acosPoolCfgByName,
    $acosMemberStat,
    $acosMemberCfg,
    $acosMemberCfgByKey,
    $acosServerStat,
    $acosServerCfg,
    $acosServerCfgByName,
    $tblAcos,
    $component,
    $components,
    $keep,
    $types
);
