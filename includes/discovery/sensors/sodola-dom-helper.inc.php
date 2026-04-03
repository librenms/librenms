<?php

/**
 * Shared helpers for Sodola transceiver DOM (string or numeric SNMP values).
 */
if (! function_exists('sodola_parse_dom_numeric')) {
    function sodola_parse_dom_numeric(mixed $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return preg_match('/-?\d+\.?\d*(?:[eE][+-]?\d+)?/', (string) $raw, $m)
            ? (float) $m[0]
            : null;
    }
}

if (! function_exists('sodola_is_plausible_dom_dbm')) {
    /**
     * Typical host-side optical levels (dBm) for discover_sensor bounds.
     */
    function sodola_is_plausible_dom_dbm(?float $v): bool
    {
        return $v !== null && $v > -80 && $v < 20;
    }
}

if (! function_exists('sodola_pick_dom_dbm')) {
    /**
     * Firmware variants expose Rx/Tx in columns 16/17 or 31/32. Prefer the first pair in range.
     *
     * @return array{0: ?float, 1: int} value and table column suffix for sensor OID
     */
    function sodola_pick_dom_dbm(?float $primary, ?float $fallback, int $primaryCol, int $fallbackCol): array
    {
        if (sodola_is_plausible_dom_dbm($primary)) {
            return [$primary, $primaryCol];
        }

        if (sodola_is_plausible_dom_dbm($fallback)) {
            return [$fallback, $fallbackCol];
        }

        return [null, $primaryCol];
    }
}
