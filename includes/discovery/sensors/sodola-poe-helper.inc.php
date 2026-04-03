<?php

/**
 * Sodola PoE: some firmware exposes table rows for non-PoE ports (uplink, SFP).
 * Optional OS allow-list (poe_ifname_regex) plus mode==2 (SNMP "not available") limits scope.
 *
 * Admin on/off is typically column 2 (0=disable, 1=enable), matching common TP-Link-style MIBs.
 * Column 6 is often capability / detection status (1/2/3) and does not match the web UI admin toggle.
 */

use App\Facades\LibrenmsConfig;

/** @var string Numeric OID prefix (no leading dot) for per-port stats: power mW, mA, V, PD (works on SL520S; .7.1.1.1 often zeros). */
if (! defined('SODOLA_POE_PORT_STATS_BASE_TRIM')) {
    define('SODOLA_POE_PORT_STATS_BASE_TRIM', '1.3.6.1.4.1.12284.5.2.1.1');
}

if (! function_exists('sodola_poe_port_stats_table')) {
    /**
     * Cached walk of .12284.5.2.1.1 (ifIndex-keyed column data; same indices as IF-MIB).
     *
     * @return array<string|int, array<string, mixed>>
     */
    function sodola_poe_port_stats_table(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $base = '.' . SODOLA_POE_PORT_STATS_BASE_TRIM;
        $resp = SnmpQuery::cache()
            ->numeric()
            ->options(['-OteQUsab', '-Pu', '-Ih'])
            ->walk($base);
        $cache = [];
        if ($resp->isValid()) {
            $resp->groupByIndex(1, $cache);
        }

        return $cache;
    }
}

if (! function_exists('sodola_poe_row_columns')) {
    /**
     * Map enterprise PoE table OID suffixes to column => value (handles multi-part row index).
     *
     * @param  array<string, mixed>  $colsFromWalk  one groupByIndex row: oid => raw
     * @return array<int, mixed>
     */
    function sodola_poe_row_columns(string $baseNumeric, array $colsFromWalk): array
    {
        $baseNumeric = ltrim($baseNumeric, '.');
        $baseParts = explode('.', $baseNumeric);
        $baseCount = count($baseParts);
        $out = [];

        foreach ($colsFromWalk as $oid => $rawRaw) {
            $oidParts = explode('.', ltrim((string) $oid, '.'));
            if (count($oidParts) < $baseCount + 1) {
                continue;
            }
            for ($i = 0; $i < $baseCount; $i++) {
                if (($oidParts[$i] ?? '') !== $baseParts[$i]) {
                    continue 2;
                }
            }
            $suffix = array_slice($oidParts, $baseCount);
            if ($suffix === []) {
                continue;
            }
            $out[(int) $suffix[0]] = $rawRaw;
        }

        // hideMib()/symbolic OIDs may not share a numeric prefix with $baseNumeric; locate the table tail after 12284.7.1.1.1.
        if ($out === [] && $colsFromWalk !== []) {
            $needle = '12284.7.1.1.1.';
            foreach ($colsFromWalk as $oid => $rawRaw) {
                $oidNorm = ltrim((string) $oid, '.');
                $pos = strpos($oidNorm, $needle);
                if ($pos === false) {
                    continue;
                }
                $tail = substr($oidNorm, $pos + strlen($needle));
                if ($tail === '') {
                    continue;
                }
                $suffix = array_values(array_filter(explode('.', $tail), fn ($p) => $p !== ''));
                if ($suffix === []) {
                    continue;
                }
                $out[(int) $suffix[0]] = $rawRaw;
            }
        }

        return $out;
    }
}

if (! function_exists('sodola_poe_has_tp_link_style_admin')) {
    /**
     * Column 2 is TP-Link-style PoE admin (0=off, 1=on) only when the agent ever reports 1 on that column.
     * On some Sodola firmware column 2 is always 0 (unused); using it would mark every port as "disabled".
     *
     * @param  array<string|int, array<string, mixed>>  $poeByIndex  SnmpResponse::groupByIndex rows
     */
    function sodola_poe_has_tp_link_style_admin(array $poeByIndex, string $baseTrim, int $adminCol): bool
    {
        foreach ($poeByIndex as $cols) {
            if (! is_array($cols)) {
                continue;
            }
            $byCol = sodola_poe_row_columns($baseTrim, $cols);
            if (isset($byCol[$adminCol]) && (int) $byCol[$adminCol] === 1) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('sodola_poe_port_is_managed')) {
    /**
     * @param  array<string, mixed>  $device
     */
    function sodola_poe_port_is_managed(array $device, string $ifName, ?int $mode): bool
    {
        if ($mode === 2) {
            return false;
        }

        $patterns = LibrenmsConfig::getOsSetting($device['os'] ?? null, 'poe_ifname_regex');
        if ($patterns === null || $patterns === '' || $patterns === []) {
            return true;
        }

        $list = is_array($patterns) ? $patterns : [$patterns];
        $list = array_values(array_filter($list, fn ($p) => is_string($p) && $p !== ''));
        if ($list === []) {
            return true;
        }

        if ($mode === null) {
            return false;
        }

        foreach ($list as $pattern) {
            if (@preg_match($pattern . 'i', $ifName)) {
                return true;
            }
        }

        return false;
    }
}
