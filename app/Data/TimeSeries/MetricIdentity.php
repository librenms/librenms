<?php

namespace App\Data\TimeSeries;

use InvalidArgumentException;

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
        if ($measurement === '') {
            throw new InvalidArgumentException('Metric measurement cannot be empty.');
        }

        foreach ($tags as $key => $value) {
            if (! is_string($key) || $key === '') {
                throw new InvalidArgumentException('Metric tag names must be non-empty strings.');
            }

            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException("Metric tag [$key] must be scalar or null.");
            }
        }
    }
}
