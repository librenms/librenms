<?php

if ($device['os'] !== 'alteonos') {
    return;
}
require_once base_path('includes/common/alteon-snmp.inc.php');

if (! function_exists('alteon_sensor_type_name')) {
    function alteon_sensor_type_name(string $type): string
    {
        $pos = strrpos($type, '::');
        $type = $pos !== false ? substr($type, $pos + 2) : $type;

        return preg_replace('/(InfoState|State)$/', '', $type) ?: $type;
    }
}

if (! function_exists('alteon_normalize_state_int')) {
    function alteon_normalize_state_int($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return max(-32768, min(32767, $intValue));
    }
}

if (! function_exists('alteon_normalize_state_definitions')) {
    function alteon_normalize_state_definitions(array $states): array
    {
        foreach ($states as &$state) {
            if (array_key_exists('value', $state)) {
                $normalized = alteon_normalize_state_int($state['value']);
                if ($normalized !== null) {
                    $state['value'] = $normalized;
                }
            }
        }
        unset($state);

        return $states;
    }
}

// Legacy SNMP helper functions replaced by SnmpQuery wrappers above.

if (! function_exists('alteon_enum_to_int')) {
    function alteon_enum_to_int($value, array $map = []): ?int
    {
        if (is_numeric($value)) {
            return alteon_normalize_state_int($value);
        }

        $value = trim((string) $value, "\" \t\n\r\0\x0B");
        if ($value === '') {
            return null;
        }

        if (preg_match('/\(([-\d]+)\)$/', $value, $matches)) {
            return alteon_normalize_state_int((int) $matches[1]);
        }

        $lower = strtolower($value);
        foreach ($map as $int => $text) {
            if ($lower === strtolower((string) $text)) {
                return alteon_normalize_state_int((int) $int);
            }
        }

        if (preg_match('/(-?\d+)\s*$/', $value, $matches)) {
            return alteon_normalize_state_int((int) $matches[1]);
        }

        return null;
    }
}

if (! function_exists('alteon_state_text_map')) {
    function alteon_state_text_map(array $states): array
    {
        $map = [];
        foreach ($states as $state) {
            if (isset($state['value'], $state['descr'])) {
                $map[$state['value']] = $state['descr'];
            }
        }

        return $map;
    }
}




if (! function_exists('alteon_format_virtual_service_label')) {
    function alteon_format_virtual_service_label(
        ?array $service,
        array $virtualServers,
        array $groupMembers,
        array $realServers,
        array $groupStatusMap,
        string $virtIndex,
        string $serviceIndex,
        ?array $memberDetail = null
    ): string {
        $serverId = $service['server_index'] ?? (string) $virtIndex;
        $virtual = $virtualServers[$serverId] ?? ['name' => '', 'vip' => ''];
        $vip = $virtual['vip'] ?? '';
        $protocol = strtoupper($service['protocol'] ?? 'TCP');
        $virtualPort = (int) ($service['virtual_port'] ?? 0);
        $realGroup = trim((string) ($service['real_group'] ?? ''));
        $realPort = (int) ($service['real_port'] ?? 0);

        $headerVip = $vip !== '' ? $vip : '-';
        $headerVport = $virtualPort > 0 ? $virtualPort : '-';
        $headerRport = $realPort > 0 ? $realPort : '-';

        $idParts = [$virtIndex, $serviceIndex];
        if ($memberDetail !== null) {
            $idParts[] = $realGroup !== '' ? $realGroup : '0';
            $idParts[] = $memberDetail['index'];
        } elseif ($realGroup !== '') {
            $idParts[] = $realGroup;
        }

        $identifier = implode('.', array_values(array_filter($idParts, fn ($part) => $part !== '' && $part !== null)));
        $identifier = $identifier !== '' ? $identifier : $virtIndex;

        $vipDisplay = $vip !== '' ? $vip : '-';
        $virtPortDisplay = $virtualPort > 0 ? $virtualPort : '-';

        $targetHost = '-';
        if ($memberDetail !== null) {
            $memberIp = trim((string) ($memberDetail['ip'] ?? ''));
            $memberFallback = $memberDetail['name'] ?? ('Real Server ' . $memberDetail['index']);
            $targetHost = $memberIp !== '' ? $memberIp : $memberFallback;
        } elseif ($realGroup !== '') {
            $targetHost = 'Group ' . $realGroup;
        }

        $targetPortDisplay = $realPort > 0 ? $realPort : '-';

        return sprintf(
            'Virt Service %s (%s | %s:%s -> %s:%s)',
            $identifier,
            $protocol,
            $vipDisplay,
            $virtPortDisplay,
            $targetHost,
            $targetPortDisplay
        );
    }
}

if (! function_exists('alteon_real_group_label')) {
    function alteon_real_group_label(string $groupIndex): string
    {
        return 'SLB Real Server Group ' . $groupIndex;
    }
}

if (! function_exists('alteon_real_group_member_details')) {
    function alteon_real_group_member_details(string $groupIndex, array $groupMembers, array $realServers): array
    {
        $details = [];
        $seen = [];
        foreach ($groupMembers[$groupIndex] ?? [] as $memberIndex) {
            $serverKey = (string) $memberIndex;
            if ($serverKey === '0' || $serverKey === '' || isset($seen[$serverKey])) {
                continue;
            }
            $seen[$serverKey] = true;

            $details[] = [
                'index' => $serverKey,
                'name' => $realServers[$serverKey]['name'] ?? "Real Server $serverKey",
                'ip' => $realServers[$serverKey]['ip'] ?? '',
            ];
        }

        return $details;
    }
}

if (! function_exists('alteon_real_group_status_text')) {
    function alteon_real_group_status_text(string $groupIndex, array $groupStatusMap): string
    {
        $states = $groupStatusMap[$groupIndex] ?? [];
        if (empty($states)) {
            return 'Unknown';
        }

        $total = count($states);
        $running = 0;
        foreach ($states as $value) {
            if ((int) $value === 1) {
                $running++;
            }
        }

        if ($running === $total) {
            return 'Ok';
        }

        if ($running === 0) {
            return 'Failed';
        }

        return 'Partially';
    }
}

echo 'Alteon ';

$realServers = alteon_real_server_definitions($device);
$realGroups = alteon_real_group_definitions($device);
$groupMembers = alteon_real_group_members($device);
$serverGroupMap = [];
foreach ($groupMembers as $groupIndex => $members) {
    $groupKey = alteon_normalize_index((string) $groupIndex) ?: (string) $groupIndex;
    foreach ((array) $members as $serverIndex) {
        $serverKey = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
        if ($serverKey === '' || $groupKey === '') {
            continue;
        }

        $serverGroupMap[$serverKey][] = $groupKey;
    }
}
$virtualServers = alteon_virtual_server_definitions($device);
$virtualServices = alteon_virtual_service_definitions($device);
$generatedVirtServiceCombos = [];
$serviceMemberMap = [];
foreach ($virtualServices as $serviceEntry) {
    $virtIdx = (string) ($serviceEntry['server_index'] ?? '');
    $serviceIdx = (string) ($serviceEntry['service_index'] ?? '');
    $groupKey = (string) ($serviceEntry['real_group'] ?? '');
    $members = array_values(array_unique($groupMembers[$groupKey] ?? []));
    $serviceMemberMap[$virtIdx][$serviceIdx] = $members;
}

// Real server runtime state (running/failed/disabled)
$realServerRuntimeIps = [];
$realInfo = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbEnhRealServerInfoEntry');
$realInfoIpKey = 'slbEnhRealServerInfoIpAddr';
if (empty($realInfo)) {
    $realInfo = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbRealServerInfoEntry');
    $realInfoIpKey = 'slbRealServerInfoIpAddr';
}

foreach ($realInfo as $index => $entry) {
    $indexKey = alteon_normalize_index((string) $index) ?: (string) $index;
    $runtimeIp = trim((string) ($entry[$realInfoIpKey] ?? ''));
    if ($runtimeIp !== '') {
        $realServerRuntimeIps[$indexKey] = $runtimeIp;
    }
}

// Real group member runtime/config state
$groupRuntime = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbOperEnhGroupRealServerEntry');
$groupStateKey = 'slbOperEnhGroupRealServerRuntimeStatus';
$groupStateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbOperEnhGroupRealServerRuntimeStatus');
$groupOidBase = '.1.3.6.1.4.1.1872.2.5.4.4.9.1.7';
$groupStates = alteon_normalize_state_definitions([
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'running'],
    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'overloaded'],
    ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
]);
if (empty($groupRuntime)) {
    $groupRuntime = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbOperGroupRealServerEntry');
    $groupStateKey = 'slbOperGroupRealServerState';
    $groupStateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbOperGroupRealServerState');
    $groupOidBase = '.1.3.6.1.4.1.1872.2.5.4.4.5.1.3';
    $groupStates = alteon_normalize_state_definitions([
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enable'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disable'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown-connection'],
        ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown-persistent-sessions'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ]);
}

$groupStatusMap = [];

if (! empty($groupRuntime)) {
    create_state_index($groupStateType, $groupStates);
    $stateMap = alteon_state_text_map($groupStates);

    foreach ($groupRuntime as $groupIndex => $servers) {
        $groupIndexRaw = (string) $groupIndex;
        $groupKey = alteon_normalize_index($groupIndexRaw) ?: $groupIndexRaw;
        $groupLabel = $realGroups[$groupKey]['label'] ?? "Real Group $groupKey";
        $memberList = $groupMembers[$groupKey] ?? array_keys((array) $servers);
        $singleMember = count($memberList) <= 1;

        foreach ((array) $servers as $serverIndex => $entry) {
            $value = alteon_enum_to_int($entry[$groupStateKey] ?? null, $stateMap);
            if ($value === null) {
                continue;
            }

            $serverIndexRaw = (string) $serverIndex;
            $serverKey = alteon_normalize_index($serverIndexRaw) ?: $serverIndexRaw;
            $groupStatusMap[$groupKey][$serverKey] = $value;
            $memberIp = $realServerRuntimeIps[$serverKey] ?? ($realServers[$serverKey]['ip'] ?? '');
            $ipDisplay = $memberIp !== '' ? $memberIp : ($realServers[$serverKey]['name'] ?? "Real Server $serverKey");
            $descr = sprintf('Real Server Group %s.%s (%s)', $groupKey, $serverKey, $ipDisplay);
            $sensorIndex = $groupKey . '.' . $serverKey;
            if ($groupStateKey === 'slbOperEnhGroupRealServerRuntimeStatus') {
                $groupOidIndex = alteon_snmp_string_index($groupKey);
                $serverOidIndex = alteon_snmp_string_index($serverKey);
            } else {
                $groupOidIndex = $groupIndexRaw;
                $serverOidIndex = $serverIndexRaw;
            }
            $oid = $groupOidBase . '.' . $groupOidIndex . '.' . $serverOidIndex;

            discover_sensor(null, 'state', $device, $oid, $sensorIndex, $groupStateType, $descr, 1, 1, null, null, null, null, $value);
        }
    }
}

// Virtual service state derived from runtime tables (per real server membership)
$virtServiceRuntime = [];
$runtimeStateKey = null;
$runtimeOidBase = null;
$runtimeSources = [
    ['entry' => 'ALTEON-CHEETAH-LAYER4-MIB::slbEnhVirtServicesInfoEntry', 'state_key' => 'slbEnhVirtServicesInfoState', 'oid_base' => '.1.3.6.1.4.1.1872.2.5.4.3.18.1.6'],
    ['entry' => 'ALTEON-CHEETAH-LAYER4-MIB::slbEnhVirtServicesInfoEntry', 'state_key' => 'slbEnhVirtServicesInfoState', 'oid_base' => '.1.3.6.1.4.1.1872.2.5.4.3.14.1.6'],
    ['entry' => 'ALTEON-CHEETAH-LAYER4-MIB::slbVirtServicesInfoEntry', 'state_key' => 'slbVirtServicesInfoState', 'oid_base' => '.1.3.6.1.4.1.1872.2.5.4.3.4.1.6'],
];
foreach ($runtimeSources as $source) {
    $virtServiceRuntime = alteon_walk_table($device, $source['entry']);
    if (! empty($virtServiceRuntime)) {
        $runtimeStateKey = $source['state_key'];
        $runtimeOidBase = $source['oid_base'];
        break;
    }
}
$runtimeStateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbVirtServicesInfoState');

if (! empty($virtServiceRuntime)) {
    $states = alteon_normalize_state_definitions([
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'blocked'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'running'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'slowstart'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'overflow'],
        ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'noinstance'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ]);
    create_state_index($runtimeStateType, $states);
    $stateMap = alteon_state_text_map($states);

    foreach ($virtServiceRuntime as $index => $entry) {
        $value = alteon_enum_to_int($entry[$runtimeStateKey] ?? null, $stateMap);
        if ($value === null) {
            continue;
        }

        if ($runtimeStateKey === 'slbEnhVirtServicesInfoState') {
            $virtIndexRaw = (string) ($entry['slbEnhVirtServicesInfoVirtServIndex'] ?? '');
            $serviceIndexRaw = (string) ($entry['slbEnhVirtServicesInfoSvcIndex'] ?? '');
            $realIndexRaw = (string) ($entry['slbEnhVirtServicesInfoRealServIndex'] ?? '');
        } else {
            $virtIndexRaw = (string) ($entry['slbVirtServicesInfoVirtServIndex'] ?? '');
            $serviceIndexRaw = (string) ($entry['slbVirtServicesInfoSvcIndex'] ?? '');
            $realIndexRaw = (string) ($entry['slbVirtServicesInfoRealServIndex'] ?? '');
        }

        if ($virtIndexRaw === '' || $serviceIndexRaw === '' || $realIndexRaw === '') {
            $parts = explode('.', (string) $index);
            $virtIndexRaw = $virtIndexRaw !== '' ? $virtIndexRaw : (string) array_shift($parts);
            $serviceIndexRaw = $serviceIndexRaw !== '' ? $serviceIndexRaw : (string) array_shift($parts);
            $realIndexRaw = $realIndexRaw !== '' ? $realIndexRaw : (string) array_shift($parts);
        }

        if ($realIndexRaw === '') {
            continue;
        }

        $virtIndex = alteon_normalize_index($virtIndexRaw) ?: $virtIndexRaw;
        $serviceIndex = (string) $serviceIndexRaw;
        $realIndex = alteon_normalize_index($realIndexRaw) ?: $realIndexRaw;

        $allowedMembers = $serviceMemberMap[$virtIndex][$serviceIndex] ?? null;
        if (is_array($allowedMembers) && $allowedMembers !== [] && ! in_array($realIndex, $allowedMembers, true)) {
            continue;
        }

        $serviceKey = $virtIndex . '.' . $serviceIndex;
        $serviceMeta = $virtualServices[$serviceKey] ?? null;
        if ($serviceMeta === null) {
            continue;
        }

        $lookupProtocol = alteon_virtual_service_protocol_lookup($device, (string) $virtIndexRaw, (string) $serviceIndexRaw);
        if ($lookupProtocol !== null) {
            $serviceMeta['protocol'] = $lookupProtocol;
            $virtualServices[$serviceKey]['protocol'] = $lookupProtocol;
        }

        $runtimeVirtPort = (int) ($entry['slbEnhVirtServicesInfoVport'] ?? $entry['slbVirtServicesInfoVport'] ?? 0);
        $runtimeRealPort = (int) ($entry['slbEnhVirtServicesInfoRport'] ?? $entry['slbVirtServicesInfoRport'] ?? 0);
        if ($runtimeVirtPort > 0) {
            $serviceMeta['virtual_port'] = $runtimeVirtPort;
        }
        if ($runtimeRealPort > 0) {
            $serviceMeta['real_port'] = $runtimeRealPort;
        }

        $memberGroup = trim((string) ($serviceMeta['real_group'] ?? ''));
        if ($memberGroup === '' && isset($serverGroupMap[$realIndex][0])) {
            $memberGroup = (string) $serverGroupMap[$realIndex][0];
        }
        if ($memberGroup !== '') {
            $serviceMeta['real_group'] = $memberGroup;
        }
        $memberIp = $realServerRuntimeIps[$realIndex] ?? ($realServers[$realIndex]['ip'] ?? '');
        $memberDetail = [
            'index' => $realIndex,
            'name' => $realServers[$realIndex]['name'] ?? "Real Server $realIndex",
            'ip' => $memberIp,
            'group' => $memberGroup,
        ];

        $groupPart = $memberGroup !== '' ? $memberGroup : '0';
        $comboKey = $virtIndex . '.' . $serviceIndex . '.' . $groupPart . '.' . $realIndex;
        if (isset($generatedVirtServiceCombos[$comboKey])) {
            continue;
        }
        $generatedVirtServiceCombos[$comboKey] = true;

        $label = alteon_format_virtual_service_label($serviceMeta, $virtualServers, $groupMembers, $realServers, $groupStatusMap, $virtIndex, $serviceIndex, $memberDetail);
        $sensorIndex = $comboKey;
        if ($runtimeStateKey === 'slbEnhVirtServicesInfoState') {
            $virtIndexOid = alteon_snmp_string_index($virtIndexRaw !== '' ? $virtIndexRaw : $virtIndex);
            $serviceIndexOid = $serviceIndex;
            $realOidIndex = alteon_snmp_string_index($realIndexRaw !== '' ? $realIndexRaw : $realIndex);
        } else {
            $virtIndexOid = $virtIndexRaw !== '' ? $virtIndexRaw : $virtIndex;
            $serviceIndexOid = $serviceIndex;
            $realOidIndex = $realIndexRaw !== '' ? $realIndexRaw : $realIndex;
        }
        $oid = $runtimeOidBase . '.' . $virtIndexOid . '.' . $serviceIndexOid . '.' . $realOidIndex;
        discover_sensor(null, 'state', $device, $oid, $sensorIndex, $runtimeStateType, $label, 1, 1, null, null, null, null, $value);
    }
}

// Fallback: if runtime data missing, keep existing sensors for backward compatibility (service-level only)
if (empty($virtServiceRuntime)) {
    $virtualServicesRaw = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServicesEntry');
    if (empty($virtualServicesRaw)) {
        $virtualServicesRaw = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServicesEntry');
    }

    if (! empty($virtualServicesRaw)) {
        $stateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServiceStatus');
        $stateKey = 'slbCurCfgEnhVirtServiceStatus';
        $oidBase = '.1.3.6.1.4.1.1872.2.5.4.1.1.4.24.1.40';
        $states = alteon_normalize_state_definitions([
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'adminDown'],
            ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'error'],
            ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
        ]);
        create_state_index($stateType, $states);
        $stateMap = alteon_state_text_map($states);

        foreach ($virtualServicesRaw as $serverIndex => $services) {
            foreach ((array) $services as $serviceIndex => $entry) {
                $value = alteon_enum_to_int($entry[$stateKey] ?? null, $stateMap);
                if ($value === null) {
                    continue;
                }

                $serviceKey = (string) $serverIndex . '.' . (string) $serviceIndex;
                $serviceMeta = $virtualServices[$serviceKey] ?? null;
                if ($serviceMeta === null) {
                    continue;
                }

                $lookupProtocol = alteon_virtual_service_protocol_lookup($device, (string) $serverIndex, (string) $serviceIndex);
                if ($lookupProtocol !== null) {
                    $serviceMeta['protocol'] = $lookupProtocol;
                    $virtualServices[$serviceKey]['protocol'] = $lookupProtocol;
                }

                $comboKey = $serviceKey . '.0';
                if (isset($generatedVirtServiceCombos[$comboKey])) {
                    continue;
                }
                $generatedVirtServiceCombos[$comboKey] = true;

                $label = alteon_format_virtual_service_label($serviceMeta, $virtualServers, $groupMembers, $realServers, $groupStatusMap, (string) $serverIndex, (string) $serviceIndex);
                $sensorIndex = $comboKey;
                $oid = $oidBase . '.' . $serverIndex . '.' . $serviceIndex;
                discover_sensor(null, 'state', $device, $oid, $sensorIndex, $stateType, $label, 1, 1, null, null, null, null, $value);
            }
        }
    }
}

// Chassis fan/temperature states (legacy scalar OIDs)
$tempStatus = alteon_snmp_get($device, 'ALTEON-CHEETAH-SWITCH-MIB::hwTemperatureStatus.0');
if ($tempStatus !== false && $tempStatus !== '') {
    $stateName = alteon_sensor_type_name('ALTEON-CHEETAH-SWITCH-MIB::hwTemperatureStatus');
    $states = alteon_normalize_state_definitions([
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'notRelevant'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'exceed'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ]);
    create_state_index($stateName, $states);
    $value = alteon_enum_to_int($tempStatus, alteon_state_text_map($states));
    if ($value !== null) {
        discover_sensor(null, 'state', $device, '.1.3.6.1.4.1.1872.2.5.1.3.1.3.0', 'alteonHwTempStatus.0', $stateName, 'Chassis Temperature Status', 1, 1, null, null, null, null, $value);
    }
}

$fanStatus = alteon_snmp_get($device, 'ALTEON-CHEETAH-SWITCH-MIB::hwFanStatus.0');
if ($fanStatus !== false && $fanStatus !== '') {
    $stateName = alteon_sensor_type_name('ALTEON-CHEETAH-SWITCH-MIB::hwFanStatus');
    $states = alteon_normalize_state_definitions([
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'notRelevant'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'fail'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'unplug'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ]);
    create_state_index($stateName, $states);
    $value = alteon_enum_to_int($fanStatus, alteon_state_text_map($states));
    if ($value !== null) {
        discover_sensor(null, 'state', $device, '.1.3.6.1.4.1.1872.2.5.1.3.1.4.0', 'alteonHwFanStatus.0', $stateName, 'Chassis Fan Status', 1, 1, null, null, null, null, $value);
    }
}
