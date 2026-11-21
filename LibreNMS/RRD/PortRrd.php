<?php

namespace LibreNMS\RRD;

use LibreNMS\Data\Store\MetricDefinitions;

/**
 * Port rrd file details
 */
final class PortRrd
{
    /**
     * Limits that cap the counters of the port metric at the port speed (in bytes per second) to drop spikes,
     * none for speeds under 10Mbps
     *
     * @return array<string, array{max: int|float}>
     */
    public static function tuneLimits(int $ifSpeed): array
    {
        if ($ifSpeed < 10000000) {
            return [];
        }

        $counters = app(MetricDefinitions::class)->of('port')?->counters() ?? [];

        return array_fill_keys($counters, ['max' => $ifSpeed / 8]);
    }
}
