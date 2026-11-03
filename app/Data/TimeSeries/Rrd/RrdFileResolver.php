<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Facades\Rrd;

/**
 * Resolves metrics to their rrd files, checking each file once
 */
class RrdFileResolver
{
    /** @var array<string, bool> relative path to whether the file exists */
    private array $exists = [];

    /**
     * Uses the same naming as the rrd datastore when writing
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity $metric): ?string
    {
        $hostname = DeviceCache::get($metric->deviceId)->hostname;

        if (empty($hostname)) {
            return null;
        }

        $path = Rrd::name($hostname, $metric->rrdName ?? $metric->measurement);
        $key = $path->relativePath();
        $this->exists[$key] ??= Rrd::checkRrdExists($path);

        return $this->exists[$key] ? $key : null;
    }
}
