<?php

/**
 * Sodola / Letscom switches:
 * - Some firmware reports ifSpeed as megabits per second (e.g. 10000 for 10G) instead of bps.
 * - ifHighSpeed may be wrong (e.g. 1410 → 1.41 Gbps) while the scaled ifSpeed matches line rate.
 */
foreach ($port_stats as &$port) {
    if (! is_array($port)) {
        continue;
    }

    if (isset($port['ifSpeed']) && is_numeric($port['ifSpeed'])) {
        $raw = (int) $port['ifSpeed'];
        // IF-MIB expects bps; values in [100, 400000] that are far below 1Gbps-as-bps are Mbps-encoded.
        if ($raw >= 100 && $raw <= 400000 && $raw < 100000000) {
            $port['ifSpeed'] = $raw * 1000000;
        }
    }

    if (! isset($port['ifSpeed'], $port['ifHighSpeed'])) {
        continue;
    }

    if (! is_numeric($port['ifSpeed']) || ! is_numeric($port['ifHighSpeed'])) {
        continue;
    }

    $bps = (int) $port['ifSpeed'];
    if ($bps <= 0) {
        continue;
    }

    $highMbps = (int) $port['ifHighSpeed'];
    $fromHigh = $highMbps * 1000000;
    if ($fromHigh <= 0) {
        continue;
    }

    // Observed Letscom bug: ifHighSpeed stuck at ~1410 (~1.41 Gbps) on 10G/VLAN interfaces.
    if ($highMbps >= 1400 && $highMbps <= 1420) {
        unset($port['ifHighSpeed']);

        continue;
    }

    if (abs($fromHigh - $bps) / $bps > 0.02) {
        unset($port['ifHighSpeed']);
    }
}
unset($port);
