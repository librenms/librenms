<?php

namespace App\Data\TimeSeries\Contracts;

use App\Data\TimeSeries\MetricIdentity;

interface MetricValidator
{
    public function validate(?MetricIdentity $metric = null, array $extra = []): ?string;

    public function hasValidFiles(): bool;

    public function hasAttempted(): bool;
}
