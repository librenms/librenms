<?php

namespace App\Data\TimeSeries\Contracts;

use App\Data\TimeSeries\MetricIdentity;

interface MetricIdentifiable
{
    /**
     * Get the metric identity for this model.
     *
     * @param  string  $metricName
     * @return MetricIdentity
     */
    public function toMetricIdentity(string $metricName): MetricIdentity;
}
