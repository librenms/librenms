<?php

namespace App\Data\TimeSeries\Contracts;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\RRD\RrdPath;

interface RrdPathResolver
{
    public function resolve(MetricIdentity $identity): RrdPath;
}
