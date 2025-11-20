<?php

if ($device['os'] !== 'alteonos') {
    return;
}

require_once base_path('includes/common/alteon-snmp.inc.php');

if (! function_exists('alteon_snmp_string_index')) {
    function alteon_snmp_string_index(string $value): string
    {
        $value = (string) $value;
        $chars = array_map('ord', str_split($value));
        array_unshift($chars, count($chars));

        return implode('.', $chars);
    }
}

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
            $rawIndex = (string) $index;
            $indexKey = alteon_normalize_index($rawIndex) ?: $rawIndex;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Server $indexKey";
            $ip = trim((string) ($entry[$ipKey] ?? ''));
            if ($ip !== '') {
                $label .= ' (' . $ip . ')';
            }

            $map[$indexKey] = [
                'label' => $label,
                'ip' => $ip,
                'index' => $indexKey,
                'oid_index' => $rawIndex,
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
            $rawIndex = (string) $index;
            $indexKey = alteon_normalize_index($rawIndex) ?: $rawIndex;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Group $indexKey";
            $map[$indexKey] = [
                'label' => $label,
                'index' => $indexKey,
                'oid_index' => $rawIndex,
            ];
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
            $groupKey = alteon_normalize_index((string) $groupIndex) ?: (string) $groupIndex;
            foreach (array_keys((array) $servers) as $serverIndex) {
                $serverKey = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
                if ($serverKey === '0' || $serverKey === '') {
                    continue;
                }

                $map[$groupKey][] = $serverKey;
            }
        }

        foreach ($map as &$membersList) {
            $membersList = array_values(array_unique($membersList));
        }
        unset($membersList);

        return $cache[$deviceId] = $map;
    }
}

if (! function_exists('alteon_virtual_server_definitions')) {
    function alteon_virtual_server_definitions(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtualServerEntry');
        $nameKey = 'slbCurCfgEnhVirtServerDname';
        $vipKey = 'slbCurCfgEnhVirtServerIpAddress';
        if (empty($entries)) {
            $entries = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtualServerEntry');
            $nameKey = 'slbCurCfgVirtServerDname';
            $vipKey = 'slbCurCfgVirtServerIpAddress';
        }

        $map = [];
        foreach ($entries as $index => $entry) {
            $rawIndex = (string) $index;
            $uid = alteon_normalize_index($rawIndex) ?: $rawIndex;
            $name = trim((string) ($entry[$nameKey] ?? $entry['slbCurCfgEnhVirtServerVname'] ?? $entry['slbCurCfgVirtServerVname'] ?? '')) ?: "Virt Server $uid";
            $vip = trim((string) ($entry[$vipKey] ?? ''));
            $label = 'Virt Server ' . $uid;
            if ($vip !== '') {
                $label .= ' (' . $vip . ')';
            }

            $map[$uid] = [
                'label' => $label,
                'name' => $name,
                'vip' => $vip,
                'index' => $uid,
                'oid_index' => $rawIndex,
            ];
        }

        return $cache[$deviceId] = $map;
    }
}

if (! function_exists('alteon_virtual_service_definitions')) {
    function alteon_virtual_service_definitions(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $entries = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServicesEntry');
        $realGroupKey = 'slbCurCfgEnhVirtServiceRealGroup';
        if (empty($entries)) {
            $entries = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServicesEntry');
            $realGroupKey = 'slbCurCfgVirtServiceRealGroup';
        }

        $map = [];
        foreach ($entries as $serverIndex => $services) {
            $serverKey = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
            foreach ((array) $services as $serviceIndex => $entry) {
                $key = $serverKey . '.' . (string) $serviceIndex;
                $map[$key] = [
                    'server_index' => $serverKey,
                    'service_index' => (string) $serviceIndex,
                    'real_group' => alteon_normalize_index((string) ($entry[$realGroupKey] ?? '')),
                ];
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
    $groupKey = alteon_normalize_index((string) $groupIndex) ?: (string) $groupIndex;
    foreach ((array) $members as $serverIndex) {
        $serverKey = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
        $serverGroupMap[$serverKey][] = $groupKey;
    }
}

// Real server current session counts
$sessionData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerCurrSessions');
$sessionKey = 'slbStatEnhRServerCurrSessions';
$sessionType = 'slbStatEnhRServerCurrSessions';
$sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.2';
$sessionIsEnh = true;
if (empty($sessionData)) {
    $sessionData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerCurrSessions');
    $sessionKey = 'slbStatRServerCurrSessions';
    $sessionType = 'slbStatRServerCurrSessions';
    $sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.2';
    $sessionIsEnh = false;
}

foreach ($sessionData as $index => $entry) {
    $value = (int) ($entry[$sessionKey] ?? $entry ?? 0);
    $entryIndex = (string) ($entry['slbStatEnhRServerIndex'] ?? $index);
    $realKey = alteon_normalize_index($entryIndex) ?: (string) $index;
    $oidIndex = $sessionIsEnh ? alteon_snmp_string_index($entryIndex !== '' ? $entryIndex : $realKey) : (string) $index;
    $heading = $realServers[$realKey]['label'] ?? ('Real Server ' . $realKey);
    $label = $heading . ' Sessions';
    $sensorIndex = $realKey;
    $oid = $sessionOidBase . '.' . $oidIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $sessionType, $label, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Real group session counts
$groupSessions = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhGroupCurrSessions');
$groupSessionKey = 'slbStatEnhGroupCurrSessions';
$groupSessionType = 'slbStatEnhGroupCurrSessions';
$groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.29.1.2';
$groupFailureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.29.1.3';
$groupSessionIsEnh = true;
if (empty($groupSessions)) {
    $groupSessions = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatGroupCurrSessions');
    $groupSessionKey = 'slbStatGroupCurrSessions';
    $groupSessionType = 'slbStatGroupCurrSessions';
    $groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.3.1.2';
    $groupFailureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.3.1.3';
    $groupSessionIsEnh = false;
}

foreach ($groupSessions as $index => $entry) {
    $value = (int) ($entry[$groupSessionKey] ?? $entry ?? 0);
    $entryIndex = (string) ($entry['slbStatEnhGroupIndex'] ?? $index);
    $groupKey = alteon_normalize_index($entryIndex) ?: (string) $index;
    $oidIndex = $groupSessionIsEnh ? alteon_snmp_string_index($entryIndex !== '' ? $entryIndex : $groupKey) : (string) $index;
    $groupLabel = $realGroups[$groupKey]['label'] ?? "Real Group $groupKey";
    $heading = $groupLabel;
    if (stripos($heading, 'real group') === 0) {
        $heading = preg_replace('/^Real Group/i', 'Real Server Group', $heading);
    } else {
        $heading = 'Real Server Group ' . $heading;
    }
    $descr = $heading . ' Sessions';
    $sensorIndex = $groupKey;
    $oid = $groupSessionOidBase . '.' . $oidIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $groupSessionType, $descr, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Real server failure counters
$failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerFailures');
$failureKey = 'slbStatEnhRServerFailures';
$failureType = 'slbStatEnhRServerFailures';
$failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.4';
$failureIsEnh = true;
if (empty($failureData)) {
    $failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerFailures');
    $failureKey = 'slbStatRServerFailures';
    $failureType = 'slbStatRServerFailures';
    $failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.4';
    $failureIsEnh = false;
}

foreach ($failureData as $index => $entry) {
    $value = (int) ($entry[$failureKey] ?? $entry ?? 0);
    $entryIndex = (string) ($entry['slbStatEnhRServerIndex'] ?? $index);
    $realKey = alteon_normalize_index($entryIndex) ?: (string) $index;
    $oidIndex = $failureIsEnh ? alteon_snmp_string_index($entryIndex !== '' ? $entryIndex : $realKey) : (string) $index;
    $heading = $realServers[$realKey]['label'] ?? ('Real Server ' . $realKey);
    $label = $heading . ' Failures';
    $sensorIndex = $realKey;
    $oid = $failureOidBase . '.' . $oidIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $failureType, $label, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);

    foreach ($serverGroupMap[$realKey] ?? [] as $groupIndex) {
        $groupFailureTotals[$groupIndex] = ($groupFailureTotals[$groupIndex] ?? 0) + $value;
    }
}

$groupLabels = $realGroups;
if (empty($groupLabels)) {
    foreach (array_keys($groupSessions) as $groupIndex) {
        $key = alteon_normalize_index((string) $groupIndex) ?: (string) $groupIndex;
        $groupLabels[$key] = [
            'label' => "Real Group $key",
            'index' => $key,
        ];
    }
}

foreach ($groupLabels as $groupIndex => $groupInfo) {
    $groupKey = alteon_normalize_index((string) ($groupInfo['index'] ?? $groupIndex)) ?: (string) $groupIndex;
    $value = $groupFailureTotals[$groupKey] ?? 0;
    $groupLabel = $groupInfo['label'] ?? "Real Group $groupKey";
    $heading = $groupLabel;
    if (stripos($heading, 'real group') === 0) {
        $heading = preg_replace('/^Real Group/i', 'Real Server Group', $heading);
    } else {
        $heading = 'Real Server Group ' . $heading;
    }
    $descr = $heading . ' Failures';
    $sensorIndex = $groupKey;
    $oidIndex = $groupSessionIsEnh ? alteon_snmp_string_index($groupKey) : (string) $groupKey;
    $oid = $groupFailureOidBase . '.' . $oidIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, 'alteonSlbGroupFailures', $descr, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $heading);
}

// Virtual service current sessions per real server member
$virtualServers = alteon_virtual_server_definitions($device);
$virtualServices = alteon_virtual_service_definitions($device);
$virtStats = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbEnhStatVirtServiceEntry');
$virtStatKey = 'slbEnhStatVirtServiceCurrSessions';
$virtStatType = 'slbEnhStatVirtServiceCurrSessions';
$virtStatOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.19.1.4';
$virtStatsEnh = true;
if (empty($virtStats)) {
    $virtStats = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatVirtServiceEntry');
    $virtStatKey = 'slbStatVirtServiceCurrSessions';
    $virtStatType = 'slbStatVirtServiceCurrSessions';
    $virtStatOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.18.1.4';
    $virtStatsEnh = false;
}

$seenVirtCounters = [];
foreach ($virtStats as $index => $entry) {
    $value = (int) ($entry[$virtStatKey] ?? $entry ?? 0);
    $virtIndexRaw = (string) ($entry['slbEnhStatVirtServerIndex'] ?? $entry['slbStatVirtServerIndex'] ?? '');
    $serviceIndex = (string) ($entry['slbEnhStatVirtServiceIndex'] ?? $entry['slbStatVirtServiceIndex'] ?? '');
    $realIndexRaw = (string) ($entry['slbEnhStatRealServerIndex'] ?? $entry['slbStatRealServerIndex'] ?? '');

    if ($virtIndexRaw === '' || $serviceIndex === '' || $realIndexRaw === '') {
        $parts = explode('.', (string) $index);
        if ($virtIndexRaw === '') {
            $virtIndexRaw = (string) array_shift($parts);
        }
        if ($serviceIndex === '' && ! empty($parts)) {
            $serviceIndex = (string) array_shift($parts);
        }
        if ($realIndexRaw === '' && ! empty($parts)) {
            $realIndexRaw = (string) array_shift($parts);
        }
    }

    if ($serviceIndex === '' || $realIndexRaw === '') {
        continue;
    }

    $virtKey = alteon_normalize_index($virtIndexRaw) ?: $virtIndexRaw;
    $realKey = alteon_normalize_index($realIndexRaw) ?: $realIndexRaw;
    $serviceKey = $virtKey . '.' . $serviceIndex;
    $serviceMeta = $virtualServices[$serviceKey] ?? null;
    $serviceGroup = $serviceMeta['real_group'] ?? '';
    if ($serviceGroup === '' && ! empty($serverGroupMap[$realKey])) {
        $serviceGroup = $serverGroupMap[$realKey][0] ?? '';
    }
    $groupPart = $serviceGroup !== '' ? $serviceGroup : '0';

    $identifier = $virtKey . '.' . $serviceIndex . '.' . $groupPart . '.' . $realKey;
    if (isset($seenVirtCounters[$identifier])) {
        continue;
    }
    $seenVirtCounters[$identifier] = true;

    $label = 'Virtual Services ' . $identifier;
    $groupLabel = 'Virtual Services ' . $virtKey . '.' . $serviceIndex;
    $sensorIndex = $identifier;
    if ($virtStatsEnh) {
        $virtOidIndex = alteon_snmp_string_index($virtIndexRaw !== '' ? $virtIndexRaw : $virtKey);
        $realOidIndex = alteon_snmp_string_index($realIndexRaw !== '' ? $realIndexRaw : $realKey);
    } else {
        $virtOidIndex = $virtIndexRaw !== '' ? $virtIndexRaw : $virtKey;
        $realOidIndex = $realIndexRaw !== '' ? $realIndexRaw : $realKey;
    }
    $oid = $virtStatOidBase . '.' . $virtOidIndex . '.' . $serviceIndex . '.' . $realOidIndex;

    discover_sensor(null, 'count', $device, $oid, $sensorIndex, $virtStatType, $label, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $groupLabel);
}
