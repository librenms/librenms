<?php

use App\Facades\LibrenmsConfig;

if ($device['os'] !== 'alteonos') {
    return;
}

if (! function_exists('alteon_mib_dirs')) {
    function alteon_mib_dirs(): array
    {
        return ['alteonos', 'radware', 'nortel'];
    }
}

if (! function_exists('alteon_mib_names')) {
    function alteon_mib_names(): array
    {
        static $mibs;

        if ($mibs !== null) {
            return $mibs;
        }

        $mibs = [];
        $installDir = rtrim(LibrenmsConfig::get('install_dir') ?: dirname(__DIR__, 4), '/');
        $mibDir = $installDir . '/mibs/alteonos';

        $addMib = static function (string $mib) use (&$mibs): void {
            if (! in_array($mib, $mibs, true)) {
                $mibs[] = $mib;
            }
        };

        foreach (['Radware', 'Nortel'] as $variant) {
            $mibFile = $mibDir . "/ALTEON-CHEETAH-LAYER4-$variant-MIB";
            if (is_file($mibFile)) {
                $addMib('+' . $mibFile);
            }
        }

        $addMib('layer4');

        return $mibs;
    }
}

if (! function_exists('alteon_snmpwalk_multi')) {
    function alteon_snmpwalk_multi($device, $oid, $array = [])
    {
        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $data = snmpwalk_cache_multi_oid($device, $oid, $array, $mibName, $mibDir);
                if (! empty($data)) {
                    return $data;
                }
            }
        }

        return [];
    }
}

if (! function_exists('alteon_snmpwalk_twopart')) {
    function alteon_snmpwalk_twopart($device, $oid, $array = [])
    {
        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $data = snmpwalk_cache_twopart_oid($device, $oid, $array, $mibName, $mibDir);
                if (! empty($data)) {
                    return $data;
                }
            }
        }

        return [];
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

        $entries = alteon_snmpwalk_multi($device, 'slbCurCfgEnhRealServerEntry');
        $nameKey = 'slbCurCfgEnhRealServerName';
        $ipKey = 'slbCurCfgEnhRealServerIpAddr';
        if (empty($entries)) {
            $entries = alteon_snmpwalk_multi($device, 'slbCurCfgRealServerEntry');
            $nameKey = 'slbCurCfgRealServerName';
            $ipKey = 'slbCurCfgRealServerIpAddr';
        }

        $map = [];
        foreach ($entries as $index => $entry) {
            $uid = (string) $index;
            $name = trim((string) ($entry[$nameKey] ?? ''));
            $label = $name !== '' ? $name : "Real Server $uid";
            $ip = $entry[$ipKey] ?? '';
            if ($ip !== '') {
                $label .= ' (' . $ip . ')';
            }

            $map[$uid] = [
                'label' => $label,
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

        $entries = alteon_snmpwalk_multi($device, 'slbCurCfgEnhGroupEntry');
        $nameKey = 'slbCurCfgEnhGroupName';
        if (empty($entries)) {
            $entries = alteon_snmpwalk_multi($device, 'slbCurCfgGroupEntry');
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

        $members = alteon_snmpwalk_twopart($device, 'slbCurCfgEnhGroupRealServerEntry');
        if (empty($members)) {
            $members = alteon_snmpwalk_twopart($device, 'slbCurCfgGroupRealServerEntry');
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

// Real server current session counts
$sessionData = alteon_snmpwalk_multi($device, 'slbStatEnhRServerCurrSessions');
$sessionKey = 'slbStatEnhRServerCurrSessions';
$sessionType = 'slbStatEnhRServerCurrSessions';
$sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.2';
if (empty($sessionData)) {
    $sessionData = alteon_snmpwalk_multi($device, 'slbStatRServerCurrSessions');
    $sessionKey = 'slbStatRServerCurrSessions';
    $sessionType = 'slbStatRServerCurrSessions';
    $sessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.2';
}

if (! empty($sessionData)) {
    foreach ($sessionData as $index => $entry) {
        $value = (int) ($entry[$sessionKey] ?? $entry ?? 0);
        $idx = (string) $index;
        $label = ($realServers[$idx]['label'] ?? "Real Server $idx") . ' Sessions';
        $sensorIndex = (string) $idx;
        $oid = $sessionOidBase . '.' . $index;

        discover_sensor(null, 'count', $device, $oid, $sensorIndex, $sessionType, $label, 1, 1, null, null, null, null, $value);
    }
}

// Real group session counts
$groupSessions = alteon_snmpwalk_multi($device, 'slbStatEnhGroupCurrSessions');
$groupSessionKey = 'slbStatEnhGroupCurrSessions';
$groupSessionType = 'slbStatEnhGroupCurrSessions';
$groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.29.1.2';
if (empty($groupSessions)) {
    $groupSessions = alteon_snmpwalk_multi($device, 'slbStatGroupCurrSessions');
    $groupSessionKey = 'slbStatGroupCurrSessions';
    $groupSessionType = 'slbStatGroupCurrSessions';
    $groupSessionOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.3.1.2';
}

if (! empty($groupSessions)) {
    foreach ($groupSessions as $index => $entry) {
        $value = (int) ($entry[$groupSessionKey] ?? $entry ?? 0);
        $idx = (string) $index;
        $groupLabel = $realGroups[$idx]['label'] ?? "Real Group $idx";
        $heading = 'SLB ' . $groupLabel;
        $descr = $heading . ' Sessions';
        $sensorIndex = (string) $idx;
        $oid = $groupSessionOidBase . '.' . $index;
        $sensorGroup = $heading;

        discover_sensor(null, 'count', $device, $oid, $sensorIndex, $groupSessionType, $descr, 1, 1, null, null, null, null, $value, 'snmp', null, null, null, $sensorGroup);
    }
}

// Real server failure counters
$failureData = alteon_snmpwalk_multi($device, 'slbStatEnhRServerFailures');
$failureKey = 'slbStatEnhRServerFailures';
$failureType = 'slbStatEnhRServerFailures';
$failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.30.1.4';
if (empty($failureData)) {
    $failureData = alteon_snmpwalk_multi($device, 'slbStatRServerFailures');
    $failureKey = 'slbStatRServerFailures';
    $failureType = 'slbStatRServerFailures';
    $failureOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.2.1.4';
}

if (! empty($failureData)) {
    foreach ($failureData as $index => $entry) {
        $value = (int) ($entry[$failureKey] ?? $entry ?? 0);
        $idx = (string) $index;
        $label = ($realServers[$idx]['label'] ?? "Real Server $idx") . ' Failures';
        $sensorIndex = (string) $idx;
        $oid = $failureOidBase . '.' . $index;

        discover_sensor(null, 'count', $device, $oid, $sensorIndex, $failureType, $label, 1, 1, null, null, null, null, $value);
    }
}

// Real server health-check failure counters per service
$hcData = alteon_snmpwalk_twopart($device, 'slbStatEnhRServerRportHCEntry');
$hcKey = 'slbStatEnhRServerRportHCFailureCount';
$hcType = 'slbStatEnhRServerRportHCFailureCount';
$hcOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.31.1.12';
if (empty($hcData)) {
    $hcData = alteon_snmpwalk_twopart($device, 'slbStatRServerRportHCEntry');
    $hcKey = 'slbStatRServerRportHCFailureCount';
    $hcType = 'slbStatRServerRportHCFailureCount';
    $hcOidBase = '.1.3.6.1.4.1.1872.2.5.4.2.28.1.12';
}

if (! empty($hcData)) {
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
}
