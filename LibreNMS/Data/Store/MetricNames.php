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
     * The names the metric had before its identity was cleaned up, null when it was always stored as identified
     */
    public function legacyOf(MetricIdentity $metric): ?LegacyMetric
    {
        return match ($metric->measurement) {
            'processor' => new LegacyMetric('processors', ['type' => 'processor_type', 'index' => 'processor_index'], ['processor', '{type}', '{index}']),
            default => null,
        };
    }
}
