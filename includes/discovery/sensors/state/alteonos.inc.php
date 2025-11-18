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

// Legacy SNMP helper functions replaced by SnmpQuery wrappers above.

if (! function_exists('alteon_enum_to_int')) {
    function alteon_enum_to_int($value, array $map = []): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $value = trim((string) $value, "\" \t\n\r\0\x0B");
        if ($value === '') {
            return null;
        }

        if (preg_match('/\(([-\d]+)\)$/', $value, $matches)) {
            return (int) $matches[1];
        }

        $lower = strtolower($value);
        foreach ($map as $int => $text) {
            if ($lower === strtolower((string) $text)) {
                return (int) $int;
            }
        }

        if (preg_match('/(-?\d+)\s*$/', $value, $matches)) {
            return (int) $matches[1];
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
            $rawName = trim((string) ($entry[$nameKey] ?? ''));
            $name = $rawName !== '' ? $rawName : "Real Server $uid";
            $ip = trim((string) ($entry[$ipKey] ?? ''));

            $labelName = $name;
            if (stripos($labelName, 'real server') === 0) {
                $labelName = trim(substr($labelName, strlen('real server')));
                if ($labelName === '') {
                    $labelName = $uid;
                }
                $labelName = 'Real Server ' . $labelName;
            }

            if (stripos($labelName, 'slb') !== 0) {
                $labelName = 'SLB ' . $labelName;
            }

            $label = $labelName;
            if ($ip !== '') {
                $label .= ' (' . $ip . ')';
            }

            $map[$uid] = [
                'label' => $label,
                'name' => $name,
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
            $map[$uid] = [
                'label' => $name !== '' ? $name : "Real Group $uid",
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
            $uid = (string) $index;
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
            ];
        }

        return $cache[$deviceId] = $map;
    }
}

if (! function_exists('alteon_virtual_service_protocol')) {
    function alteon_virtual_service_protocol(?int $value): string
    {
        return match ($value) {
            2 => 'UDP',
            3 => 'TCP',
            4 => 'STATELESS',
            5 => 'TCP+UDP',
            6 => 'SCTP',
            default => 'TCP',
        };
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
        $portKey = 'slbCurCfgEnhVirtServiceVirtPort';
        $realGroupKey = 'slbCurCfgEnhVirtServiceRealGroup';
        $realPortKey = 'slbCurCfgEnhVirtServiceRealPort';
        $protoKey = 'slbCurCfgEnhVirtServiceUDPBalance';
        $nameKey = 'slbCurCfgEnhVirtServiceDname';
        if (empty($entries)) {
            $entries = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServicesEntry');
            $portKey = 'slbCurCfgVirtServiceVirtPort';
            $realGroupKey = 'slbCurCfgVirtServiceRealGroup';
            $realPortKey = 'slbCurCfgVirtServiceRealPort';
            $protoKey = 'slbCurCfgVirtServiceUDPBalance';
            $nameKey = 'slbCurCfgVirtServiceHname';
        }

        $map = [];
        $protoEnumMap = [
            2 => 'udp',
            3 => 'tcp',
            4 => 'stateless',
            5 => 'tcpandudp',
            6 => 'sctp',
        ];

        foreach ($entries as $serverIndex => $services) {
            foreach ((array) $services as $serviceIndex => $entry) {
                $key = (string) $serverIndex . '.' . (string) $serviceIndex;
                $protoValue = alteon_enum_to_int($entry[$protoKey] ?? null, $protoEnumMap);
                $protocol = $protoValue !== null ? alteon_virtual_service_protocol($protoValue) : null;
                if ($protocol === null) {
                    $protocol = alteon_virtual_service_protocol_lookup($device, (string) $serverIndex, (string) $serviceIndex) ?? 'TCP';
                }

                $map[$key] = [
                    'server_index' => (string) $serverIndex,
                    'service_index' => (string) $serviceIndex,
                    'virtual_port' => (int) ($entry[$portKey] ?? 0),
                    'real_group' => (string) ($entry[$realGroupKey] ?? ''),
                    'real_port' => (int) ($entry[$realPortKey] ?? 0),
                    'protocol' => $protocol,
                    'name' => trim((string) ($entry[$nameKey] ?? '')),
                ];
            }
        }

        return $cache[$deviceId] = $map;
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

if (! function_exists('alteon_virtual_service_protocol_lookup')) {
    function alteon_virtual_service_protocol_lookup($device, string $virtIndex, string $serviceIndex): ?string
    {
        $oids = [
            "ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServiceUDPBalance.$virtIndex.$serviceIndex",
            "ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServiceUDPBalance.$virtIndex.$serviceIndex",
        ];
        $protoEnumMap = [
            2 => 'udp',
            3 => 'tcp',
            4 => 'stateless',
            5 => 'tcpandudp',
            6 => 'sctp',
        ];

        foreach ($oids as $oid) {
            $raw = alteon_snmp_get($device, $oid);
            if ($raw !== false && $raw !== '') {
                $value = alteon_enum_to_int($raw, $protoEnumMap);
                if ($value !== null) {
                    return alteon_virtual_service_protocol($value);
                }
            }
        }

        return null;
    }
}

echo 'Alteon ';

$realServers = alteon_real_server_definitions($device);
$realGroups = alteon_real_group_definitions($device);
$groupMembers = alteon_real_group_members($device);
$serverGroupMap = [];
foreach ($groupMembers as $groupIndex => $members) {
    foreach ($members as $serverIndex) {
        $serverGroupMap[$serverIndex] = $groupIndex;
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
    $indexKey = (string) $index;
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
$groupStates = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'running'],
    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'overloaded'],
    ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
];
if (empty($groupRuntime)) {
    $groupRuntime = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbOperGroupRealServerEntry');
    $groupStateKey = 'slbOperGroupRealServerState';
    $groupStateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbOperGroupRealServerState');
    $groupOidBase = '.1.3.6.1.4.1.1872.2.5.4.4.5.1.3';
    $groupStates = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enable'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disable'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown-connection'],
        ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown-persistent-sessions'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ];
}

$groupStatusMap = [];

if (! empty($groupRuntime)) {
    create_state_index($groupStateType, $groupStates);
    $stateMap = alteon_state_text_map($groupStates);

    foreach ($groupRuntime as $groupIndex => $servers) {
        $groupKey = (string) $groupIndex;
        $groupLabel = $realGroups[$groupKey]['label'] ?? "Real Group $groupKey";
        $memberList = $groupMembers[$groupKey] ?? array_keys((array) $servers);
        $singleMember = count($memberList) <= 1;

        foreach ((array) $servers as $serverIndex => $entry) {
            $value = alteon_enum_to_int($entry[$groupStateKey] ?? null, $stateMap);
            if ($value === null) {
                continue;
            }

            $serverKey = (string) $serverIndex;
            $groupStatusMap[$groupKey][$serverKey] = $value;
            $memberIp = $realServerRuntimeIps[$serverKey] ?? ($realServers[$serverKey]['ip'] ?? '');
            $ipDisplay = $memberIp !== '' ? $memberIp : ($realServers[$serverKey]['name'] ?? "Real Server $serverKey");
            $descr = sprintf('Real Server Group %s.%s (%s)', $groupKey, $serverKey, $ipDisplay);
            $sensorIndex = $groupKey . '.' . $serverKey;
            $oid = $groupOidBase . '.' . $groupIndex . '.' . $serverIndex;

            discover_sensor(null, 'state', $device, $oid, $sensorIndex, $groupStateType, $descr, 1, 1, null, null, null, null, $value);
        }
    }
}

// Virtual service state derived from runtime tables (per real server membership)
$virtServiceRuntime = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbEnhVirtServicesInfoEntry');
$runtimeStateType = alteon_sensor_type_name('ALTEON-CHEETAH-LAYER4-MIB::slbVirtServicesInfoState');
$runtimeStateKey = 'slbEnhVirtServicesInfoState';
$runtimeOidBase = '.1.3.6.1.4.1.1872.2.5.4.3.14.1.6';
if (empty($virtServiceRuntime)) {
    $virtServiceRuntime = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbVirtServicesInfoEntry');
    $runtimeStateKey = 'slbVirtServicesInfoState';
    $runtimeOidBase = '.1.3.6.1.4.1.1872.2.5.4.3.4.1.6';
}

if (! empty($virtServiceRuntime)) {
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'blocked'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'running'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'slowstart'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'overflow'],
        ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'noinstance'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ];
    create_state_index($runtimeStateType, $states);
    $stateMap = alteon_state_text_map($states);

    foreach ($virtServiceRuntime as $index => $entry) {
        $value = alteon_enum_to_int($entry[$runtimeStateKey] ?? null, $stateMap);
        if ($value === null) {
            continue;
        }

        $parts = explode('.', (string) $index);
        $virtIndex = (string) array_shift($parts);
        $serviceIndex = (string) array_shift($parts);
        $realIndex = (string) array_shift($parts);
        if ($realIndex === '') {
            continue;
        }

        $allowedMembers = $serviceMemberMap[$virtIndex][$serviceIndex] ?? [];
        if (empty($allowedMembers) || ! in_array($realIndex, $allowedMembers, true)) {
            continue;
        }

        $serviceKey = $virtIndex . '.' . $serviceIndex;
        $serviceMeta = $virtualServices[$serviceKey] ?? null;
        if ($serviceMeta === null) {
            continue;
        }

        $memberGroup = (string) ($serviceMeta['real_group'] ?? '');
        if ($memberGroup === '') {
            $memberGroup = (string) ($serverGroupMap[$realIndex] ?? '');
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
        $oid = $runtimeOidBase . '.' . $index;
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
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'adminDown'],
            ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'shutdown'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'error'],
            ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
        ];
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
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'notRelevant'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'exceed'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ];
    create_state_index($stateName, $states);
    $value = alteon_enum_to_int($tempStatus, alteon_state_text_map($states));
    if ($value !== null) {
        discover_sensor(null, 'state', $device, '.1.3.6.1.4.1.1872.2.5.1.3.1.3.0', 'alteonHwTempStatus.0', $stateName, 'Chassis Temperature Status', 1, 1, null, null, null, null, $value);
    }
}

$fanStatus = alteon_snmp_get($device, 'ALTEON-CHEETAH-SWITCH-MIB::hwFanStatus.0');
if ($fanStatus !== false && $fanStatus !== '') {
    $stateName = alteon_sensor_type_name('ALTEON-CHEETAH-SWITCH-MIB::hwFanStatus');
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'notRelevant'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'fail'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'unplug'],
        ['value' => 2147483647, 'generic' => 3, 'graph' => 0, 'descr' => 'unsupported'],
    ];
    create_state_index($stateName, $states);
    $value = alteon_enum_to_int($fanStatus, alteon_state_text_map($states));
    if ($value !== null) {
        discover_sensor(null, 'state', $device, '.1.3.6.1.4.1.1872.2.5.1.3.1.4.0', 'alteonHwFanStatus.0', $stateName, 'Chassis Fan Status', 1, 1, null, null, null, null, $value);
    }
}
