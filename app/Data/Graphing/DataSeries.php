<?php

namespace App\Data\Graphing;

use App\Data\TimeSeries\MetricIdentity;

readonly class DataSeries
{
    public function __construct(
        public MetricIdentity $metric,
        public string $field,
        public string $description,
) {}
}
