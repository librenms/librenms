<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\Rrd;
use LibreNMS\RRD\RrdPath;

/**
 * Resolves metrics to their existing rrd files, checking each file once
 */
class RrdFileResolver
{
    /** @var array<string, bool> relative path to whether the file exists */
    private array $exists = [];

    public function __construct(private readonly RrdLayout $layout = new RrdLayout)
    {
    }

    /**
     * Prefers the file the poller writes to, a metric stored under its other name is still found
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity $metric): ?string
    {
        foreach ($this->layout->paths($metric) as $path) {
            if ($this->exists($path)) {
                return $path->relativePath();
            }
        }

        return null;
    }

    private function exists(RrdPath $path): bool
    {
        return $this->exists[$path->relativePath()] ??= Rrd::checkRrdExists($path);
    }
}
