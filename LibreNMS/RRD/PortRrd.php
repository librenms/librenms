<?php

namespace LibreNMS\RRD;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Data\Store\MetricDefinitions;
use LibreNMS\Data\Store\MetricNames;

/**
 * Port rrd file details
 */
final class PortRrd
{
    /**
     * Limits that cap the counters of the port metric at the port speed (in bytes per second) to drop spikes,
     * none for speeds under 10Mbps
     *
     * @return array<string, array{max: int|float}> by data source name
     */
    public static function tuneLimits(MetricIdentity $port, int $ifSpeed): array
    {
        if ($ifSpeed < 10000000) {
            return [];
        }

        $names = app(MetricNames::class);
        $limits = [];
        foreach (app(MetricDefinitions::class)->of($port)?->counters() ?? [] as $field) {
            $limits[$names->storedField($port, $field)] = ['max' => $ifSpeed / 8];
        }

        return $limits;
    }
}
