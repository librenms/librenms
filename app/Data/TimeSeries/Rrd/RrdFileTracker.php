<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Facades\Rrd;
use LibreNMS\RRD\RrdPath;

/**
 * Resolves metrics to rrd files and remembers which of the files a graph needs exist
 */
class RrdFileTracker
{
    private bool $checked = false;
    /** @var array<string, bool> relative path to whether the file exists */
    private array $exists = [];

    /**
     * Checks each file once, unknown devices count as a missing file.
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity|RrdPath $metric): ?string
    {
        $this->checked = true;

        $path = $metric instanceof RrdPath ? $metric : $this->resolve($metric);

        if ($path === null) {
            return null;
        }

        $key = $path->relativePath();
        $this->exists[$key] ??= Rrd::checkRrdExists($path);

        return $this->exists[$key] ? $key : null;
    }

    /**
     * Files were checked and none exist
     */
    public function foundNoFiles(): bool
    {
        return $this->checked && ! in_array(true, $this->exists, true);
    }

    /**
     * Same naming as the rrd datastore uses when writing
     */
    private function resolve(MetricIdentity $metric): ?RrdPath
    {
        $hostname = DeviceCache::get($metric->deviceId)->hostname;

        return empty($hostname) ? null : Rrd::name($hostname, $metric->rrdName ?? $metric->measurement);
    }
}
