<?php

namespace App\Data\TimeSeries;

/**
 * Identifies a stored metric independently of the datastore.
 * Mirrors the arguments to Datastore::put(): device, measurement and tags.
 * rrdName is the optional rrd_name override, rrd files are named after the measurement when it is null.
 */
final readonly class MetricIdentity
{
    /**
     * @param  array<string, scalar|null>  $tags
     * @param  string|list<string|int>|null  $rrdName
     */
    public function __construct(
        public int $deviceId,
        public string $measurement,
        public array $tags = [],
        public string|array|null $rrdName = null,
    ) {
    }
}
