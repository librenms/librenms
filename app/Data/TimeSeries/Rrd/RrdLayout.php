<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\RRD\RrdPath;

/**
 * Where the rrd file of a metric is kept.
 * Files of a measurement that was named differently before keep working under the legacy name until they are renamed.
 */
class RrdLayout
{
    /**
     * The file of a metric: in the directory of its group, named after the measurement and the other tag values.
     */
    public function path(MetricIdentity $metric): ?RrdPath
    {
        return $this->file($metric, [$metric->measurement, ...array_values($metric->tagsInGroup())]);
    }

    /**
     * The file a metric was kept in before its measurement was renamed, null when it only ever had one name.
     */
    public function legacyPath(MetricIdentity $metric): ?RrdPath
    {
        $name = $this->legacyName($metric->measurement, $metric->tags);

        return $name === null ? null : $this->file($metric, $name);
    }

    /**
     * @param  list<scalar|null>  $name
     */
    private function file(MetricIdentity $metric, array $name): ?RrdPath
    {
        $directory = $metric->group();

        return $directory === '' ? null : RrdPath::make($directory, implode('-', $name) . '.rrd');
    }

    /**
     * @param  array<string, scalar>  $tags
     * @return list<scalar|null>|null
     */
    private function legacyName(string $measurement, array $tags): ?array
    {
        return match ($measurement) {
            'processors' => ['processor', $tags['type'] ?? null, $tags['index'] ?? null],
            default => null,
        };
    }
}
