<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\MetricNaming;
use LibreNMS\RRD\RrdPath;

/**
 * Where the rrd file of a metric is kept
 */
class RrdLayout
{
    public function __construct(private readonly MetricNaming $naming = new MetricNaming)
    {
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
        $legacy = $this->naming->previous($metric);
        $paths = [
            $this->path($metric),
            $legacy === null ? null : $this->file($metric, $legacy->rrdNameOf($metric) ?? [$legacy->measurement]),
        ];

        return array_values(array_filter($this->naming->isCanonical() ? $paths : array_reverse($paths)));
    }

    /**
     * @param  list<scalar>  $name
     */
    private function file(MetricIdentity $metric, array $name): ?RrdPath
    {
        $directory = $metric->group();

        return $directory === '' ? null : RrdPath::make($directory, implode('-', $name) . '.rrd');
    }
}
