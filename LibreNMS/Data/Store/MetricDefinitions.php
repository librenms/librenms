<?php

namespace LibreNMS\Data\Store;

use App\Data\TimeSeries\MetricDefinition;
use App\Data\TimeSeries\MetricField;
use App\Data\TimeSeries\MetricIdentity;

/**
 * The fields of each measurement, by the measurement of its identity.
 * Datastores use them to store a metric (the rrd data sources), readers to know which fields are counters and their limits.
 * Fields have clean names, the names they were stored under before are kept in MetricNames.
 */
class MetricDefinitions
{
    public function of(MetricIdentity|string $metric): ?MetricDefinition
    {
        return match ($metric instanceof MetricIdentity ? $metric->measurement : $metric) {
            'port' => new MetricDefinition(...array_map(fn (string $name): MetricField => MetricField::derive($name, 0, 12500000000), [
                'in_octets',
                'out_octets',
                'in_errors',
                'out_errors',
                'in_ucast_pkts',
                'out_ucast_pkts',
                'in_nucast_pkts',
                'out_nucast_pkts',
                'in_discards',
                'out_discards',
                'in_unknown_protos',
                'in_broadcast_pkts',
                'out_broadcast_pkts',
                'in_multicast_pkts',
                'out_multicast_pkts',
            ])),
            'processor' => new MetricDefinition(MetricField::gauge('usage', -273, 1000)),
            default => null,
        };
    }

    public function field(MetricIdentity|string $metric, string $field): ?MetricField
    {
        return $this->of($metric)?->field($field);
    }
}
