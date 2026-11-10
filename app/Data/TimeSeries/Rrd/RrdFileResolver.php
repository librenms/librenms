<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\LegacyMetric;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\Rrd;
use Closure;
use LibreNMS\RRD\RrdPath;

/**
 * Names the rrd files of a metric and resolves them to the existing one, checking each file once
 */
class RrdFileResolver
{
    /** @var array<string, bool> relative path to whether the file exists */
    private array $exists = [];

    /**
     * @param  (Closure(MetricIdentity): ?LegacyMetric)|null  $previousNames  the names a metric had before, by default the registry of LegacyMetric
     */
    public function __construct(private readonly ?Closure $previousNames = null)
    {
    }

    /**
     * Prefers the file the poller writes to, a metric stored under its other name is still found
     *
     * @return string|null The path to the RRD file, relative to the rrd directory, if it exists, null otherwise.
     */
    public function pathFor(MetricIdentity $metric): ?string
    {
        foreach ($this->paths($metric) as $path) {
            if ($this->exists($path)) {
                return $path->relativePath();
            }
        }

        return null;
    }

    /**
     * The file of a metric: in the directory of its group, named after the measurement and the other tag values.
     */
    public function path(MetricIdentity $metric): ?RrdPath
    {
        return $this->file($metric, [$metric->measurement, ...array_values($metric->tagsInGroup())]);
    }

    /**
     * The files of a metric, the one the poller writes to first.
     * A metric whose identity was cleaned up may still be kept in the file it had before.
     *
     * @return list<RrdPath>
     */
    public function paths(MetricIdentity $metric): array
    {
        $legacy = $this->previousNames === null ? LegacyMetric::previous($metric) : ($this->previousNames)($metric);
        $paths = [
            $this->path($metric),
            $legacy === null ? null : $this->file($metric, $legacy->rrdNameOf($metric) ?? [$legacy->measurement]),
        ];

        return array_values(array_filter(LegacyMetric::isCanonical() ? $paths : array_reverse($paths)));
    }

    /**
     * @param  list<scalar>  $name
     */
    private function file(MetricIdentity $metric, array $name): ?RrdPath
    {
        $directory = $metric->group();

        return $directory === '' ? null : RrdPath::make($directory, implode('-', $name) . '.rrd');
    }

    private function exists(RrdPath $path): bool
    {
        return $this->exists[$path->relativePath()] ??= Rrd::checkRrdExists($path);
    }
}
