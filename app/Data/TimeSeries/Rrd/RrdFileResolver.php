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
     * Prefers the current name of the file, a file that was not renamed yet is read under its legacy name
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity $metric): ?string
    {
        $path = $this->layout->path($metric);
        if ($path !== null && $this->exists($path)) {
            return $path->relativePath();
        }

        $legacyPath = $this->layout->legacyPath($metric);
        if ($legacyPath !== null && $this->exists($legacyPath)) {
            return $legacyPath->relativePath();
        }

        return null;
    }

    private function exists(RrdPath $path): bool
    {
        return $this->exists[$path->relativePath()] ??= Rrd::checkRrdExists($path);
    }
}
