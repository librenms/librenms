<?php

namespace LibreNMS\Interfaces\Models;

use App\Data\TimeSeries\MetricIdentity;

interface HasMetrics
{
    /**
     * Identity used when storing and reading a measurement of this model
     */
    public function metricIdentity(string $measurement): MetricIdentity;
}
