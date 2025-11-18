<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Source\SnmpQueryInterface;

if (! function_exists('alteon_snmp')) {
    function alteon_snmp(array $device): SnmpQueryInterface
    {
        return \SnmpQuery::deviceArray($device)
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

        if (! empty($rows)) {
            return $rows;
        }

        return alteon_legacy_walk_table($device, $oid);
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

        if (! empty($table)) {
            return $table;
        }

        return alteon_legacy_walk_twopart($device, $oid);
    }
}

if (! function_exists('alteon_snmp_get')) {
    function alteon_snmp_get(array $device, string $oid)
    {
        $value = alteon_snmp($device)->get($oid)->value();

        if ($value !== null && $value !== '' && stripos((string) $value, 'No Such') === false) {
            return $value;
        }

        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $legacy = snmp_get($device, $oid, '-OQv', $mibName, $mibDir);
                if ($legacy !== false && $legacy !== '' && stripos((string) $legacy, 'No Such') === false) {
                    return $legacy;
                }
            }
        }

        return false;
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
        $installDir = rtrim(LibrenmsConfig::get('install_dir') ?: base_path(), '/');
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

if (! function_exists('alteon_legacy_walk_table')) {
    function alteon_legacy_walk_table(array $device, array|string $oid): array
    {
        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $data = snmpwalk_cache_multi_oid($device, $oid, [], $mibName, $mibDir);
                if (! empty($data)) {
                    return $data;
                }
            }
        }

        return [];
    }
}

if (! function_exists('alteon_legacy_walk_twopart')) {
    function alteon_legacy_walk_twopart(array $device, array|string $oid): array
    {
        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $data = snmpwalk_cache_twopart_oid($device, $oid, [], $mibName, $mibDir);
                if (! empty($data)) {
                    return $data;
                }
            }
        }

        return [];
    }
}
