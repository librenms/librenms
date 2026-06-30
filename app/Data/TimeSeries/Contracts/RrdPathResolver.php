<?php

namespace App\Data\TimeSeries\Contracts;

use App\Data\TimeSeries\MetricIdentity;

interface RrdPathResolver
{
    public function resolve(MetricIdentity $identity): string;
}
