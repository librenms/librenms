<?php

namespace App\Data\TimeSeries;

/**
 * Identifies a stored metric independently of the datastore: a measurement and the tags of one series of it.
 * What a series belongs to is a tag like any other, usually hostname.
 * Only tags that tell series apart belong here, a tag that may change (a description) would start a new series.
 */
final readonly class MetricIdentity
{
    /**
     * @param  array<string, scalar>  $tags  in a fixed order, datastores may name a series after the values
     */
    public function __construct(
        public string $measurement,
        public array $tags = [],
    ) {
    }
}
