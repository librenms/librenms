<?php

if ($device['os'] !== 'alteonos') {
    return;
}

require_once base_path('includes/common/alteon-snmp.inc.php');

if (! function_exists('alteon_real_server_definitions')) {
    function alteon_real_server_definitions(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhRealServerEntry');
        $nameKey = 'slbCurCfgEnhRealServerName';
        $ipKey = 'slbCurCfgEnhRealServerIpAddr';
        if (empty($entries)) {
            $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgRealServerEntry');
            $nameKey = 'slbCurCfgRealServerName';
            $ipKey = 'slbCurCfgRealServerIpAddr';
        }

        $map = [];
        foreach ($entries as $index => $entry) {
            $uid = (string) $index;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Server $uid";
            $ip = trim((string) ($entry[$ipKey] ?? ''));
            if ($ip !== '') {
                $label .= ' (' . $ip . ')';
            }

            $map[$uid] = [
                'label' => $label,
                'ip' => $ip,
            ];
        }

        return $cache[$deviceId] = $map;
    }
}

if (! function_exists('alteon_real_group_definitions')) {
    function alteon_real_group_definitions(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhGroupEntry');
        $nameKey = 'slbCurCfgEnhGroupName';
        if (empty($entries)) {
            $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgGroupEntry');
            $nameKey = 'slbCurCfgGroupName';
        }

        $map = [];
        foreach ($entries as $index => $entry) {
            $uid = (string) $index;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $map[$uid] = ['label' => $name !== '' ? $name : "Real Group $uid"];
        }

        return $cache[$deviceId] = $map;
    }
}

if (! function_exists('alteon_real_group_members')) {
    function alteon_real_group_members(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $members = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhGroupRealServerEntry');
        if (empty($members)) {
            $members = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgGroupRealServerEntry');
        }

        $map = [];
        foreach ($members as $groupIndex => $servers) {
            foreach (array_keys((array) $servers) as $serverIndex) {
                $serverKey = (string) $serverIndex;
                if ($serverKey === '0' || $serverKey === '') {
                    continue;
                }

                $map[(string) $groupIndex][] = $serverKey;
            }
        }

        return $cache[$deviceId] = $map;
    }
}

echo 'Alteon ';

$realServers = alteon_real_server_definitions($device);
$realGroups = alteon_real_group_definitions($device);
$groupMembers = alteon_real_group_members($device);
$groupFailureTotals = [];
$serverGroupMap = [];
foreach ($groupMembers as $groupIndex => $members) {
    foreach ((array) $members as $serverIndex) {
        $serverGroupMap[(string) $serverIndex][] = (string) $groupIndex;
    }
}

// Real server current session counts
$sessionData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerCurrSessions');
$sessionKey = 'slbStatEnhRServerCurrSessions';
$sessionType = 'slbStatEnhRServerCurrSessions';
$sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.2';
if (empty($sessionData)) {
    $sessionData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerCurrSessions');
    $sessionKey = 'slbStatRServerCurrSessions';
    $sessionType = 'slbStatRServerCurrSessions';
    $sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.2';
}

foreach ($sessionData as $index => $entry) {
    $value = (int) ($entry[$sessionKey] ?? $entry ?? 0);
    $idx = (string) $index;
    $heading = 'Real Server ' . $idx;
    $ip = $realServers[$idx]['ip'] ?? '';
    if ($ip !== '') {
        $heading .= ' (' . $ip . ')';
    }
    $label = $heading . ' Sessions';
    $sensorIndex = (string) $idx;
    $oid = $sessionOidBase . '.' . $index;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $sessionType, $label, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Real group session counts
$groupSessions = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhGroupCurrSessions');
$groupSessionKey = 'slbStatEnhGroupCurrSessions';
$groupSessionType = 'slbStatEnhGroupCurrSessions';
$groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.29.1.2';
$groupFailureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.29.1.3';
if (empty($groupSessions)) {
    $groupSessions = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatGroupCurrSessions');
    $groupSessionKey = 'slbStatGroupCurrSessions';
    $groupSessionType = 'slbStatGroupCurrSessions';
    $groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.3.1.2';
    $groupFailureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.3.1.3';
}

foreach ($groupSessions as $index => $entry) {
    $value = (int) ($entry[$groupSessionKey] ?? $entry ?? 0);
    $idx = (string) $index;
    $groupLabel = $realGroups[$idx]['label'] ?? "Real Group $idx";
    $heading = 'SLB ' . $groupLabel;
    $descr = $heading . ' Sessions';
    $sensorIndex = (string) $idx;
    $oid = $groupSessionOidBase . '.' . $index;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $groupSessionType, $descr, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Real server failure counters
$failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerFailures');
$failureKey = 'slbStatEnhRServerFailures';
$failureType = 'slbStatEnhRServerFailures';
$failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.4';
if (empty($failureData)) {
    $failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerFailures');
    $failureKey = 'slbStatRServerFailures';
    $failureType = 'slbStatRServerFailures';
    $failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.4';
}

foreach ($failureData as $index => $entry) {
    $value = (int) ($entry[$failureKey] ?? $entry ?? 0);
    $idx = (string) $index;
    $heading = 'Real Server ' . $idx;
    $ip = $realServers[$idx]['ip'] ?? '';
    if ($ip !== '') {
        $heading .= ' (' . $ip . ')';
    }
    $label = $heading . ' Failures';
    $sensorIndex = (string) $idx;
    $oid = $failureOidBase . '.' . $index;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $failureType, $label, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);

    foreach ($serverGroupMap[$idx] ?? [] as $groupIndex) {
        $groupFailureTotals[$groupIndex] = ($groupFailureTotals[$groupIndex] ?? 0) + $value;
    }
}

$groupLabels = $realGroups;
if (empty($groupLabels)) {
    foreach (array_keys($groupSessions) as $groupIndex) {
        $groupLabels[(string) $groupIndex] = ['label' => "Real Group $groupIndex"];
    }
}

foreach ($groupLabels as $groupIndex => $groupInfo) {
    $value = $groupFailureTotals[$groupIndex] ?? 0;
    $groupLabel = $groupInfo['label'] ?? "Real Group $groupIndex";
    $heading = 'SLB ' . $groupLabel;
    $descr = $heading . ' Failures';
    $sensorIndex = (string) $groupIndex;
    $oid = $groupFailureOidBase . '.' . $groupIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, 'alteonSlbGroupFailures', $descr, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Real server health-check failure counters per service
$hcData = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerRportHCEntry');
$hcKey = 'slbStatEnhRServerRportHCFailureCount';
$hcType = 'slbStatEnhRServerRportHCFailureCount';
$hcOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.31.1.12';
if (empty($hcData)) {
    $hcData = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerRportHCEntry');
    $hcKey = 'slbStatRServerRportHCFailureCount';
    $hcType = 'slbStatRServerRportHCFailureCount';
    $hcOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.28.1.12';
}

foreach ($hcData as $realIndex => $services) {
    $realKey = (string) $realIndex;
    $realLabel = $realServers[$realKey]['label'] ?? "Real Server $realKey";

    foreach ((array) $services as $serviceIndex => $entry) {
        $value = (int) ($entry[$hcKey] ?? 0);
        $serviceKey = (string) $serviceIndex;
        $descr = $realLabel . ' / Service ' . $serviceKey . ' HC Failures';
        $sensorIndex = $realKey . '.' . $serviceKey;
        $oid = $hcOidBase . '.' . $realIndex . '.' . $serviceIndex;

        discover_sensor(null, 'count', $device, $oid, $sensorIndex, $hcType, $descr, 1, 1, null, null, null, null, $value);
    }
}
