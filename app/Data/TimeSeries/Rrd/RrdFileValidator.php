<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Facades\Rrd;
use LibreNMS\Data\Store\Rrd as RrdStore;
use LibreNMS\RRD\RrdPath;

/**
 * Resolves metrics to rrd files and tracks which exist
 */
class RrdFileValidator
{
    /** @var array<string, string|null> */
    private array $validatedRrdFiles = [];

    /**
     * Resolve a metric identity or rrd path and check if the RRD file exists.
     * Caches the result to avoid multiple filesystem checks for the same file.
     *
     * @return string|null The path to the RRD file (relative when using rrdcached) if it exists, null otherwise.
     */
    public function validate(MetricIdentity|RrdPath $metric): ?string
    {
        $path = $metric instanceof RrdPath ? $metric : $this->resolve($metric);

        if ($path === null) {
            $this->validatedRrdFiles["device:$metric->deviceId"] = null;

            return null;
        }

        $key = $path->relativePath();

        if (! array_key_exists($key, $this->validatedRrdFiles)) {
            $this->validatedRrdFiles[$key] = Rrd::checkRrdExists($path) ? (string) $path : null;
        }

        return $this->validatedRrdFiles[$key];
    }

    /**
     * Files were checked and none exist
     */
    public function foundNoFiles(): bool
    {
        return ! empty($this->validatedRrdFiles) && empty(array_filter($this->validatedRrdFiles));
    }

    /**
     * Same naming as the rrd datastore uses when writing
     */
    private function resolve(MetricIdentity $metric): ?RrdPath
    {
        $hostname = DeviceCache::get($metric->deviceId)->hostname;

        if (empty($hostname)) {
            return null;
        }

        return RrdPath::make($hostname, RrdStore::filenameString($metric->rrdName ?? $metric->measurement) . '.rrd');
    }
}
