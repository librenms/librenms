<?php

namespace App\Data\TimeSeries;

use App\Facades\LibrenmsConfig;

/**
 * Decides the names a metric is stored under.
 * Metrics are identified by clean names, datastores keep the names they always used
 * until datastore.canonical_metrics says the stored data was moved to the clean names.
 */
class MetricNaming
{
    /**
     * The names to store the metric under instead of its identity, null when it is stored as identified
     */
    public function legacy(MetricIdentity $metric): ?LegacyMetric
    {
        return $this->isCanonical() ? null : $this->previous($metric);
    }

    public function isCanonical(): bool
    {
        return (bool) LibrenmsConfig::get('datastore.canonical_metrics', false);
    }

    /**
     * The names the metric had before it was cleaned up, null when it was always stored as identified
     */
    public function previous(MetricIdentity $metric): ?LegacyMetric
    {
        return match ($metric->measurement) {
            'processor' => new LegacyMetric('processors', ['type' => 'processor_type', 'index' => 'processor_index'], ['processor', '{type}', '{index}']),
            default => null,
        };
    }
}
