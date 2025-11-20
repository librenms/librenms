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
