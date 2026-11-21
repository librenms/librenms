<?php

namespace LibreNMS\Data\Store;

use App\Data\TimeSeries\MetricDefinition;
use App\Data\TimeSeries\MetricField;
use App\Data\TimeSeries\MetricIdentity;

/**
 * The fields of each measurement, by the measurement of its identity.
 * Datastores use them to store a metric (the rrd data sources), readers to know which fields are counters and their limits.
 * Fields are named as they are stored.
 */
class MetricDefinitions
{
    public function of(MetricIdentity|string $metric): ?MetricDefinition
    {
        return match ($metric instanceof MetricIdentity ? $metric->measurement : $metric) {
            'port' => new MetricDefinition(...array_map(fn (string $name): MetricField => MetricField::derive($name, 0, 12500000000), [
                'INOCTETS',
                'OUTOCTETS',
                'INERRORS',
                'OUTERRORS',
                'INUCASTPKTS',
                'OUTUCASTPKTS',
                'INNUCASTPKTS',
                'OUTNUCASTPKTS',
                'INDISCARDS',
                'OUTDISCARDS',
                'INUNKNOWNPROTOS',
                'INBROADCASTPKTS',
                'OUTBROADCASTPKTS',
                'INMULTICASTPKTS',
                'OUTMULTICASTPKTS',
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
