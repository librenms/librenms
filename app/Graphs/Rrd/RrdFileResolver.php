<?php

namespace App\Graphs\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\Rrd;

/**
 * Finds the existing rrd file of a metric, checking each file once
 */
class RrdFileResolver
{
    /** @var array<string, bool> relative path to whether the file exists */
    private array $exists = [];

    /**
     * Prefers the file the poller writes to, a metric stored under its other name is still found
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity $metric): ?string
    {
        foreach (Rrd::metricNames($metric) as $path) {
            $file = $path->relativePath();

            if ($this->exists[$file] ??= Rrd::checkRrdExists($path)) {
                return $file;
            }
        }

        return null;
    }
}
