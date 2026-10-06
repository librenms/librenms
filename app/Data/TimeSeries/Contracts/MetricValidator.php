<?php

namespace App\Data\TimeSeries\Contracts;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\RRD\RrdPath;

interface MetricValidator
{
    public function validate(MetricIdentity|RrdPath $metric): ?string;

    public function hasValidFiles(): bool;

    public function hasAttempted(): bool;
}
