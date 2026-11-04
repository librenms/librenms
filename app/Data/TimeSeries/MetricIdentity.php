<?php

namespace App\Data\TimeSeries;

/**
 * Identifies a stored metric independently of the datastore: what it belongs to, the measurement and its tags.
 */
final readonly class MetricIdentity
{
    /**
     * @param  string  $owner  the hostname of a device, or a name for a metric that outlives being on one device, such as the cluster of a VM
     * @param  array<string, scalar|null>  $tags
     */
    public function __construct(
        public string $owner,
        public string $measurement,
        public array $tags = [],
    ) {
    }

    /**
     * The tags of the series in a datastore, the owner keeps a series together when the device writing it changes.
     *
     * @return array<string, scalar|null>
     */
    public function seriesTags(): array
    {
        return ['owner' => $this->owner, ...$this->tags];
    }
}
