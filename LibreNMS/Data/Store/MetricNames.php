<?php

namespace LibreNMS\Data\Store;

use App\Data\TimeSeries\LegacyMetric;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;

/**
 * The names metrics are stored under.
 * Metrics are identified by clean names, datastores keep the names they always used
 * until datastore.canonical_metrics says the stored data was moved to the clean names.
 */
class MetricNames
{
    private const array SENSOR_TAGS = ['class' => 'sensor_class', 'type' => 'sensor_type', 'index' => 'sensor_index', 'descr' => 'sensor_descr'];
    private const array PORT_FIELDS = [
        'in_octets' => 'INOCTETS',
        'out_octets' => 'OUTOCTETS',
        'in_errors' => 'INERRORS',
        'out_errors' => 'OUTERRORS',
        'in_ucast_pkts' => 'INUCASTPKTS',
        'out_ucast_pkts' => 'OUTUCASTPKTS',
        'in_nucast_pkts' => 'INNUCASTPKTS',
        'out_nucast_pkts' => 'OUTNUCASTPKTS',
        'in_discards' => 'INDISCARDS',
        'out_discards' => 'OUTDISCARDS',
        'in_unknown_protos' => 'INUNKNOWNPROTOS',
        'in_broadcast_pkts' => 'INBROADCASTPKTS',
        'out_broadcast_pkts' => 'OUTBROADCASTPKTS',
        'in_multicast_pkts' => 'INMULTICASTPKTS',
        'out_multicast_pkts' => 'OUTMULTICASTPKTS',
    ];

    public function isCanonical(): bool
    {
        return (bool) LibrenmsConfig::get('datastore.canonical_metrics', false);
    }

    /**
     * The names the metric is stored under instead of its identity, null when it is stored as identified
     */
    public function storedAs(MetricIdentity $metric): ?LegacyMetric
    {
        return $this->isCanonical() ? null : $this->legacyOf($metric);
    }

    /**
     * The name a field of the metric is stored under
     */
    public function storedField(MetricIdentity $metric, string $field): string
    {
        return $this->storedAs($metric)?->fieldName($field) ?? $field;
    }

    /**
     * The names the metric had before its identity was cleaned up, null when it was always stored as identified
     */
    public function legacyOf(MetricIdentity $metric): ?LegacyMetric
    {
        return match ($metric->measurement) {
            'port' => new LegacyMetric('ports', rrdName: ['port-id{id}'], fields: self::PORT_FIELDS),
            'processor' => new LegacyMetric('processors', ['type' => 'processor_type', 'index' => 'processor_index'], ['processor', '{type}', '{index}']),
            'mempool' => new LegacyMetric('mempool', ['type' => 'mempool_type', 'class' => 'mempool_class', 'index' => 'mempool_index'], ['mempool', '{type}', '{class}', '{index}']),
            // ipmi sensors were a measurement of their own, except in rrd files
            'sensor' => new LegacyMetric(($metric->tags['type'] ?? null) === 'ipmi' ? 'ipmi' : 'sensor', self::SENSOR_TAGS, ['sensor', ...array_map(fn ($tag) => '{' . $tag . '}', array_keys($metric->tagsInGroup()))]),
            'ping' => new LegacyMetric('icmp-perf'),
            default => null,
        };
    }
}
