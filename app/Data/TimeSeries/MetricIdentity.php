<?php

namespace App\Data\TimeSeries;

/**
 * Identifies a stored metric independently of the datastore: a measurement and the tags of one series of it.
 * The identity is authoritative: it is how a metric is named, the names a metric had before its identity was cleaned up
 * are kept in MetricNames. While datastore.canonical_metrics is false, Datastore::write stores the metric under those
 * legacy measurement and tag names (MetricNames::storedAs), and readers also find data that is still stored under them.
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

    /**
     * What datastores that keep series in a hierarchy (rrd, graphite) group this series under:
     * the hostname, or the first tag of a metric that does not belong to one device.
     */
    public function group(): string
    {
        $tag = $this->groupTag();

        return $tag === null ? '' : (string) $this->tags[$tag];
    }

    /**
     * The tags that tell the series apart inside its group
     *
     * @return array<string, scalar>
     */
    public function tagsInGroup(): array
    {
        return array_diff_key($this->tags, [(string) $this->groupTag() => null]);
    }

    private function groupTag(): ?string
    {
        $first = array_key_first($this->tags);

        assert($first === 'hostname' || ! array_key_exists('hostname', $this->tags), 'hostname tag should be first!');

        return $first;
    }
}
