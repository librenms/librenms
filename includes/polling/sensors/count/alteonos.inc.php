<?php

require_once base_path('includes/common/alteon-snmp.inc.php');

if ($sensor['sensor_type'] === 'alteonSlbGroupFailures') {
    $groupFailures = alteonos_collect_group_failures($device);
    $sensor_value = $groupFailures[(string) $sensor['sensor_index']] ?? 0;
}

if (! function_exists('alteonos_collect_group_failures')) {
    function alteonos_collect_group_failures(array $device): array
    {
        static $cache = [];

        $deviceId = (string) ($device['device_id'] ?? 0);
        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $groupMembers = alteonos_get_group_members($device);
        $serverGroups = [];
        foreach ($groupMembers as $groupIndex => $members) {
            foreach ((array) $members as $serverIndex) {
                $normalizedGroup = alteon_normalize_index((string) $groupIndex) ?: (string) $groupIndex;
                $normalizedServer = alteon_normalize_index((string) $serverIndex) ?: (string) $serverIndex;
                $serverGroups[$normalizedServer][] = $normalizedGroup;
            }
        }

        $failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatEnhRServerEntry');
        $failureKey = 'slbStatEnhRServerFailures';
        if (empty($failureData)) {
            $failureData = alteon_walk_table($device, 'ALTEON-CHEETAH-LAYER4-MIB::slbStatRServerEntry');
            $failureKey = 'slbStatRServerFailures';
        }

        $totals = [];
        foreach ($failureData as $index => $entry) {
            $value = (int) ($entry[$failureKey] ?? $entry ?? 0);
            $serverIdx = alteon_normalize_index((string) ($entry['slbStatEnhRServerIndex'] ?? $index)) ?: (string) $index;
            foreach ($serverGroups[$serverIdx] ?? [] as $groupIdx) {
                $totals[$groupIdx] = ($totals[$groupIdx] ?? 0) + $value;
            }
        }

        return $cache[$deviceId] = $totals;
    }
}

if (! function_exists('alteonos_get_group_members')) {
    function alteonos_get_group_members(array $device): array
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

        return $cache[$deviceId] = $map;
    }
}
