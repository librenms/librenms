<?php

/*
 * LibreNMS module to poll A10 ACOS SLB (Server Load Balancing) statistics
 *
 * Re-reads the same stat tables used at discovery time and writes
 * counters/gauges to RRD via the standard Datastore, keyed off the
 * components created by
 * includes/discovery/loadbalancers/a10-acos-slb.inc.php
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;

$component = new LibreNMS\Component();
$options['filter']['disabled'] = ['=', 0];
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']] ?? [];

$types = ['acos-slb-vs', 'acos-slb-vport', 'acos-slb-pool', 'acos-slb-member', 'acos-slb-server'];
$keep = [];
foreach ($components as $k => $v) {
    if (in_array($v['type'], $types)) {
        $keep[$k] = $v;
    }
}
$components = $keep;

if (! empty($components)) {
    $acosAppBase = '1.3.6.1.4.1.22610.2.4.3';

    // Core LibreNMS's Component::setComponentPrefs() always logs its own generic
    // "Component <id> has been modified: status => X" message (hardcoded in
    // vendor code, unavoidable while status is persisted) - not useful for
    // finding which of ~10,000 objects actually changed. Log our own friendly,
    // identifiable message alongside it, including the same Component ID so
    // the two can be correlated (or ours read alone, without needing the DB).
    if (! function_exists('acos_slb_log_status_change')) {
        function acos_slb_log_status_change($device, $id, array $array, $oldStatus)
        {
            if ($oldStatus === $array['status']) {
                return;
            }

            $what = match ($array['type']) {
                'acos-slb-vs' => "Virtual Server '{$array['label']}' ({$array['address']})",
                'acos-slb-vport' => "Virtual Service '{$array['label']}'",
                'acos-slb-pool' => "Service Group '{$array['label']}'",
                'acos-slb-member' => "Pool Member '{$array['servername']}:{$array['port']}' in Service Group '{$array['poolname']}'",
                'acos-slb-server' => "Real Server '{$array['label']}' ({$array['address']})",
                default => "Component '{$array['label']}'",
            };

            $severity = match ($array['status']) {
                2 => Severity::Error,
                1 => Severity::Warning,
                default => Severity::Ok,
            };
            $state = $array['status'] == 0 ? 'Ok' : $array['error'];

            Eventlog::log("Component $id: $what is now: $state", $device['device_id'], 'acos-slb', $severity, $id);
        }
    }

    // l7reqs/l7succreqs/l7currreqs change on almost every poll for active
    // objects. Keep them in their own RRD file (never merged into $array /
    // setComponentPrefs) so routine counter churn doesn't flood the Eventlog
    // with "Attribute X was modified" entries every poll cycle.
    if (! function_exists('acos_slb_put_l7_rrd')) {
        function acos_slb_put_l7_rrd($device, $type, $label, $hash, $row)
        {
            $rrd_def = RrdDefinition::make()
                ->addDataset('l7reqs', 'COUNTER', 0)
                ->addDataset('l7succreqs', 'COUNTER', 0)
                ->addDataset('l7currreqs', 'GAUGE', 0);

            $fields = [
                'l7reqs' => $row['l7reqs'],
                'l7succreqs' => $row['l7succreqs'],
                'l7currreqs' => $row['l7currreqs'],
            ];

            $tags = ['rrd_name' => [$type, 'l7', $label, $hash], 'rrd_def' => $rrd_def, 'type' => $type . '-l7', 'hash' => $hash, 'label' => $label];
            app('Datastore')->put($device, $type . '-l7', $tags, $fields);
        }
    }

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

    // Same helper used by discovery - kept local to this file so the poller
    // can run standalone without depending on the discovery include.
    // Returns null if any walk failed; components of an unread table keep
    // their previous status instead of being marked healthy.
    if (! function_exists('acos_slb_walk_table')) {
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

    $acosVsStat = acos_slb_walk_table($device, $acosAppBase . '.4.2.1.1', [
        'address' => 1, 'name' => 2, 'pktsin' => 3, 'bytesin' => 4, 'pktsout' => 5,
        'bytesout' => 6, 'persistconns' => 7, 'totconns' => 8, 'curconns' => 9,
        'status' => 10, 'displaystatus' => 11, 'l7reqs' => 12, 'l7currreqs' => 13,
        'l7succreqs' => 14, 'peakconns' => 15,
    ], 'address');

    $acosVportStat = acos_slb_walk_table($device, $acosAppBase . '.4.4.1.1', [
        'address' => 1, 'porttype' => 2, 'portnum' => 3, 'name' => 4, 'status' => 5,
        'pktsin' => 6, 'bytesin' => 7, 'pktsout' => 8, 'bytesout' => 9,
        'persistconns' => 10, 'totconns' => 11, 'curconns' => 12, 'displaystatus' => 13,
        'l7reqs' => 14, 'l7currreqs' => 15, 'l7succreqs' => 16, 'peakconns' => 17,
    ], 'address');

    $acosPoolStat = acos_slb_walk_table($device, $acosAppBase . '.3.2.1.1', [
        'name' => 1, 'pktsin' => 2, 'bytesin' => 3, 'pktsout' => 4, 'bytesout' => 5,
        'totconns' => 6, 'curconns' => 7, 'persistconns' => 8, 'displaystatus' => 9,
        'l7reqs' => 10, 'l7currreqs' => 11, 'l7succreqs' => 12, 'peakconns' => 13,
    ], 'name');

    $acosMemberStat = acos_slb_walk_table($device, $acosAppBase . '.3.4.1.1', [
        'poolname' => 1, 'addrtype' => 2, 'servername' => 3, 'portnum' => 4,
        'pktsin' => 5, 'bytesin' => 6, 'pktsout' => 7, 'bytesout' => 8,
        'persistconns' => 9, 'totconns' => 10, 'curconns' => 11, 'status' => 12,
        'l7reqs' => 13, 'l7currreqs' => 14, 'l7succreqs' => 15, 'responsetime' => 16,
        'peakconns' => 17,
    ], 'poolname');

    $acosServerStat = acos_slb_walk_table($device, $acosAppBase . '.2.2.2.1', [
        'address' => 1, 'name' => 2, 'pktsin' => 3, 'bytesin' => 4, 'pktsout' => 5,
        'bytesout' => 6, 'totconns' => 7, 'curconns' => 8, 'persistconns' => 9,
        'status' => 10, 'l7reqs' => 11, 'l7currreqs' => 12, 'l7succreqs' => 13,
        'peakconns' => 14,
    ], 'address');

    foreach ($components as $key => &$array) {
        $type = $array['type'];
        $UID = $array['UID'];
        $label = $array['label'];
        $hash = $array['hash'];
        $rrd_name = [$type, $label, $hash];

        if ($type == 'acos-slb-vs') {
            $row = $acosVsStat[$UID] ?? null;
            if ($row === null) {
                continue;
            }

            $rrd_def = RrdDefinition::make()
                ->addDataset('pktsin', 'COUNTER', 0)
                ->addDataset('pktsout', 'COUNTER', 0)
                ->addDataset('bytesin', 'COUNTER', 0)
                ->addDataset('bytesout', 'COUNTER', 0)
                ->addDataset('totconns', 'COUNTER', 0)
                ->addDataset('curconns', 'GAUGE', 0)
                ->addDataset('peakconns', 'GAUGE', 0);

            $fields = [
                'pktsin' => $row['pktsin'],
                'pktsout' => $row['pktsout'],
                'bytesin' => $row['bytesin'],
                'bytesout' => $row['bytesout'],
                'totconns' => $row['totconns'],
                'curconns' => $row['curconns'],
                'peakconns' => $row['peakconns'],
            ];

            $array['displaystatus'] = $row['displaystatus'];
            $oldStatus = $array['status'] ?? null;
            if ($row['status'] === null) {
                // status column had no value for this row: unknown, keep the previous status
            } elseif ($row['status'] == 2) {
                $array['status'] = 2;
                $array['error'] = 'Virtual Server is Down';
            } elseif ($row['status'] == 3) {
                $array['status'] = 1;
                $array['error'] = 'Virtual Server is Disabled';
            } elseif ($row['displaystatus'] == 3) {
                $array['status'] = 1;
                $array['error'] = 'Virtual Server is partially up';
            } else {
                $array['status'] = 0;
                $array['error'] = '';
            }
            acos_slb_log_status_change($device, $key, $array, $oldStatus);
            acos_slb_put_l7_rrd($device, $type, $label, $hash, $row);
        } elseif ($type == 'acos-slb-vport') {
            $row = $acosVportStat[$UID] ?? null;
            if ($row === null) {
                continue;
            }

            $rrd_def = RrdDefinition::make()
                ->addDataset('pktsin', 'COUNTER', 0)
                ->addDataset('pktsout', 'COUNTER', 0)
                ->addDataset('bytesin', 'COUNTER', 0)
                ->addDataset('bytesout', 'COUNTER', 0)
                ->addDataset('totconns', 'COUNTER', 0)
                ->addDataset('curconns', 'GAUGE', 0)
                ->addDataset('peakconns', 'GAUGE', 0);

            $fields = [
                'pktsin' => $row['pktsin'],
                'pktsout' => $row['pktsout'],
                'bytesin' => $row['bytesin'],
                'bytesout' => $row['bytesout'],
                'totconns' => $row['totconns'],
                'curconns' => $row['curconns'],
                'peakconns' => $row['peakconns'],
            ];

            $oldStatus = $array['status'] ?? null;
            if ($row['status'] === null) {
                // status column had no value for this row: unknown, keep the previous status
            } elseif ($row['status'] == 2) {
                $array['status'] = 2;
                $array['error'] = 'Virtual Server Port is Down';
            } elseif ($row['status'] == 3) {
                $array['status'] = 1;
                $array['error'] = 'Virtual Server Port is Disabled';
            } else {
                $array['status'] = 0;
                $array['error'] = '';
            }
            acos_slb_log_status_change($device, $key, $array, $oldStatus);
            acos_slb_put_l7_rrd($device, $type, $label, $hash, $row);
        } elseif ($type == 'acos-slb-pool') {
            $row = $acosPoolStat[$UID] ?? null;
            if ($row === null) {
                continue;
            }

            $rrd_def = RrdDefinition::make()
                ->addDataset('pktsin', 'COUNTER', 0)
                ->addDataset('pktsout', 'COUNTER', 0)
                ->addDataset('bytesin', 'COUNTER', 0)
                ->addDataset('bytesout', 'COUNTER', 0)
                ->addDataset('totconns', 'COUNTER', 0)
                ->addDataset('curconns', 'GAUGE', 0)
                ->addDataset('peakconns', 'GAUGE', 0);

            $fields = [
                'pktsin' => $row['pktsin'],
                'pktsout' => $row['pktsout'],
                'bytesin' => $row['bytesin'],
                'bytesout' => $row['bytesout'],
                'totconns' => $row['totconns'],
                'curconns' => $row['curconns'],
                'peakconns' => $row['peakconns'],
            ];

            $oldStatus = $array['status'] ?? null;
            if ($row['displaystatus'] === null) {
                // status column had no value for this row: unknown, keep the previous status
            } elseif ($row['displaystatus'] == 4) {
                $array['status'] = 2;
                $array['error'] = 'Service Group is Stopped (no members up)';
            } elseif ($row['displaystatus'] == 3) {
                $array['status'] = 1;
                $array['error'] = 'Service Group is only Partially Up';
            } else {
                $array['status'] = 0;
                $array['error'] = '';
            }
            acos_slb_log_status_change($device, $key, $array, $oldStatus);
            acos_slb_put_l7_rrd($device, $type, $label, $hash, $row);
        } elseif ($type == 'acos-slb-member') {
            $row = $acosMemberStat[$UID] ?? null;
            if ($row === null) {
                continue;
            }

            $rrd_def = RrdDefinition::make()
                ->addDataset('pktsin', 'COUNTER', 0)
                ->addDataset('pktsout', 'COUNTER', 0)
                ->addDataset('bytesin', 'COUNTER', 0)
                ->addDataset('bytesout', 'COUNTER', 0)
                ->addDataset('totconns', 'COUNTER', 0)
                ->addDataset('curconns', 'GAUGE', 0)
                ->addDataset('peakconns', 'GAUGE', 0)
                ->addDataset('responsetime', 'GAUGE', 0);

            $fields = [
                'pktsin' => $row['pktsin'],
                'pktsout' => $row['pktsout'],
                'bytesin' => $row['bytesin'],
                'bytesout' => $row['bytesout'],
                'totconns' => $row['totconns'],
                'curconns' => $row['curconns'],
                'peakconns' => $row['peakconns'],
                'responsetime' => $row['responsetime'],
            ];

            $oldStatus = $array['status'] ?? null;
            if ($row['status'] === null) {
                // status column had no value for this row: unknown, keep the previous status
            } elseif ($row['status'] == 2) {
                $array['status'] = 2;
                $array['error'] = 'Pool Member is Down';
            } elseif ($row['status'] == 0) {
                $array['status'] = 1;
                $array['error'] = 'Pool Member is Disabled';
            } else {
                $array['status'] = 0;
                $array['error'] = '';
            }
            acos_slb_log_status_change($device, $key, $array, $oldStatus);
            // Member-level l7reqs/l7succreqs aren't shown in the UI - not persisted or graphed.
        } elseif ($type == 'acos-slb-server') {
            $row = $acosServerStat[$UID] ?? null;
            if ($row === null) {
                continue;
            }

            $rrd_def = RrdDefinition::make()
                ->addDataset('pktsin', 'COUNTER', 0)
                ->addDataset('pktsout', 'COUNTER', 0)
                ->addDataset('bytesin', 'COUNTER', 0)
                ->addDataset('bytesout', 'COUNTER', 0)
                ->addDataset('totconns', 'COUNTER', 0)
                ->addDataset('curconns', 'GAUGE', 0)
                ->addDataset('peakconns', 'GAUGE', 0);

            $fields = [
                'pktsin' => $row['pktsin'],
                'pktsout' => $row['pktsout'],
                'bytesin' => $row['bytesin'],
                'bytesout' => $row['bytesout'],
                'totconns' => $row['totconns'],
                'curconns' => $row['curconns'],
                'peakconns' => $row['peakconns'],
            ];

            $oldStatus = $array['status'] ?? null;
            if ($row['status'] === null) {
                // status column had no value for this row: unknown, keep the previous status
            } elseif ($row['status'] == 2) {
                $array['status'] = 2;
                $array['error'] = 'Real Server is Down';
            } elseif ($row['status'] == 0) {
                $array['status'] = 1;
                $array['error'] = 'Real Server is Disabled';
            } else {
                $array['status'] = 0;
                $array['error'] = '';
            }
            acos_slb_log_status_change($device, $key, $array, $oldStatus);
            acos_slb_put_l7_rrd($device, $type, $label, $hash, $row);
        } else {
            d_echo('Type is unknown: ' . $type . "\n");
            continue;
        }

        d_echo("\n\nComponent: " . $key . "\n");
        d_echo('    Type: ' . $type . "\n");
        d_echo('    Label: ' . $label . "\n");

        $tags = ['rrd_name' => $rrd_name, 'rrd_def' => $rrd_def, 'type' => $type, 'hash' => $hash, 'label' => $label];
        app('Datastore')->put($device, $type, $tags, $fields);
    } // End foreach components
    unset($array);

    // Write the Components back to the DB (status/error changes above).
    $component->setComponentPrefs($device['device_id'], $components);
}

unset(
    $type,
    $components,
    $component,
    $options,
    $keep,
    $types,
    $acosAppBase,
    $acosVsStat,
    $acosVportStat,
    $acosPoolStat,
    $acosMemberStat,
    $acosServerStat
);
