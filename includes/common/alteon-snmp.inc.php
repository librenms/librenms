<?php

use LibreNMS\Data\Source\SnmpQueryInterface;

if (! function_exists('alteon_snmp')) {
    function alteon_snmp(array $device): SnmpQueryInterface
    {
        return \SnmpQuery::cache()
            ->deviceArray($device)
            ->mibDir('alteonos')
            ->mibDir('radware')
            ->mibDir('nortel')
            ->mibs([
                'ALTEON-CHEETAH-LAYER4-Nortel-MIB',
                'ALTEON-CHEETAH-LAYER4-Radware-MIB',
                'ALTEON-CHEETAH-LAYER4-MIB',
                'ALTEON-CHEETAH-SWITCH-MIB',
                'ALTEON-ROOT-MIB',
            ]);
    }
}

if (! function_exists('alteon_walk_table')) {
    function alteon_walk_table(array $device, array|string $oid): array
    {
        $rows = alteon_snmp($device)->walk($oid)->valuesByIndex();

        foreach ($rows as $index => $entry) {
            $rows[$index] = alteon_normalize_row((array) $entry);
        }

        return $rows;
    }
}

if (! function_exists('alteon_walk_twopart')) {
    function alteon_walk_twopart(array $device, array|string $oid): array
    {
        $table = alteon_snmp($device)->walk($oid)->table(2);

        foreach ($table as $indexA => $entries) {
            foreach ($entries as $indexB => $entry) {
                $table[$indexA][$indexB] = alteon_normalize_row((array) $entry);
            }
        }

        return $table;
    }
}

if (! function_exists('alteon_snmp_get')) {
    function alteon_snmp_get(array $device, string $oid)
    {
        $value = alteon_snmp($device)->get($oid)->value();

        if ($value === null || $value === '' || stripos((string) $value, 'No Such') !== false) {
            return false;
        }

        return $value;
    }
}

if (! function_exists('alteon_normalize_row')) {
    function alteon_normalize_row(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $value;
                continue;
            }

            $shortKey = str_contains((string) $key, '::') ? substr((string) $key, strrpos((string) $key, '::') + 2) : $key;
            $normalized[$shortKey] = $value;
        }

        return $normalized;
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
            $uid = alteon_normalize_index($rawIndex) ?: $rawIndex;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Server $uid";
            $ip = trim((string) ($entry[$ipKey] ?? ''));
            if ($ip !== '') {
                $label .= ' (' . $ip . ')';
            }

            $map[$uid] = [
                'label' => $label,
                'ip' => $ip,
                'index' => $uid,
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
            $uid = alteon_normalize_index($rawIndex) ?: $rawIndex;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Group $uid";
            $map[$uid] = [
                'label' => $label,
                'index' => $uid,
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
        $protoEnumMap = [
            2 => 'udp',
            3 => 'tcp',
            4 => 'stateless',
            5 => 'tcpandudp',
            6 => 'sctp',
        ];

        $usedEnhanced = ! empty($entries);

        foreach ($entries as $serverIndex => $services) {
            $serverOidIndex = (string) $serverIndex;
            $serverKey = alteon_normalize_index($serverOidIndex) ?: $serverOidIndex;
            foreach ((array) $services as $serviceIndex => $entry) {
                $key = $serverKey . '.' . (string) $serviceIndex;
                $protoValue = alteon_enum_to_int($entry['slbCurCfgEnhVirtServiceUDPBalance'] ?? $entry['slbCurCfgVirtServiceUDPBalance'] ?? null, $protoEnumMap);
                $protocol = $protoValue !== null
                    ? alteon_virtual_service_protocol($protoValue)
                    : alteon_virtual_service_protocol_lookup($device, (string) $serverIndex, (string) $serviceIndex);
                $map[$key] = [
                    'server_index' => $serverKey,
                    'server_oid_index' => $serverOidIndex,
                    'service_index' => (string) $serviceIndex,
                    'real_group' => alteon_normalize_index((string) ($entry[$realGroupKey] ?? '')),
                    'virtual_port' => (int) ($entry['slbCurCfgEnhVirtServiceVirtPort'] ?? $entry['slbCurCfgVirtServiceVirtPort'] ?? 0),
                    'real_port' => (int) ($entry['slbCurCfgEnhVirtServiceRealPort'] ?? $entry['slbCurCfgVirtServiceRealPort'] ?? 0),
                    'protocol' => $protocol,
                ];
            }
        }

        if ($usedEnhanced) {
            $legacyEntries = alteon_walk_twopart($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServicesEntry');
            foreach ($legacyEntries as $serverIndex => $services) {
                $serverKey = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
                foreach ((array) $services as $serviceIndex => $entry) {
                    $key = $serverKey . '.' . (string) $serviceIndex;
                    if (! isset($map[$key])) {
                        continue;
                    }

                    $protoValue = alteon_enum_to_int($entry['slbCurCfgVirtServiceUDPBalance'] ?? null, $protoEnumMap);
                    if ($protoValue !== null) {
                        $map[$key]['protocol'] = alteon_virtual_service_protocol($protoValue);
                    }
                }
            }
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
        };
    }
}

if (! function_exists('alteon_virtual_service_protocol_lookup')) {
    function alteon_virtual_service_protocol_lookup($device, string $virtIndex, string $serviceIndex): ?string
    {
        $encodedVirtIndex = alteon_snmp_string_index($virtIndex);
        $oids = [
            "ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServiceUDPBalance.$encodedVirtIndex.$serviceIndex",
            "ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgEnhVirtServiceUDPBalance.$virtIndex.$serviceIndex",
            "ALTEON-CHEETAH-LAYER4-MIB::slbCurCfgVirtServiceUDPBalance.$virtIndex.$serviceIndex",
        ];
        $protoEnumMap = [
            2 => 'UDP',
            3 => 'TCP',
            4 => 'STATELESS',
            5 => 'TCP+UDP',
            6 => 'SCTP',
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

if (! function_exists('alteon_snmp_string_index')) {
    function alteon_snmp_string_index(string $value): string
    {
        $value = (string) $value;
        $chars = array_map('ord', str_split($value));
        array_unshift($chars, count($chars));

        return implode('.', $chars);
    }
}

if (! function_exists('alteon_decode_display_index')) {
    function alteon_decode_display_index(string $index): string
    {
        $index = trim($index);
        if ($index === '' || ! str_contains($index, '.')) {
            return $index;
        }

        $parts = array_map('intval', explode('.', $index));
        if (count($parts) < 2) {
            return $index;
        }

        $length = array_shift($parts);
        if ($length <= 0 || $length > count($parts)) {
            return $index;
        }

        $chars = array_slice($parts, 0, $length);
        if (count($chars) !== $length) {
            return $index;
        }

        $decoded = '';
        foreach ($chars as $char) {
            if ($char < 32 || $char > 126) {
                return $index;
            }

            $decoded .= chr($char);
        }

        if (count($parts) !== $length) {
            return $index;
        }

        return trim($decoded);
    }
}

if (! function_exists('alteon_normalize_index')) {
    function alteon_normalize_index(?string $index): string
    {
        $index = trim((string) $index);
        if ($index === '') {
            return '';
        }

        $decoded = alteon_decode_display_index($index);

        return $decoded !== '' ? $decoded : $index;
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
