<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\Rrd;
use LibreNMS\RRD\RrdPath;

/**
 * Where the rrd file of a metric is kept.
 * Files of a measurement that was named differently before keep working under the legacy name until they are renamed.
 */
class RrdLayout
{
    private const string DIRECTORY_TAG = 'hostname';

    /**
     * The file of a metric: in the directory of its hostname, named after the measurement and the other tag values.
     * A metric that does not belong to one device is kept in the directory of its first tag.
     */
    public function path(MetricIdentity $metric): ?RrdPath
    {
        $tags = $metric->tags;
        $directoryTag = array_key_exists(self::DIRECTORY_TAG, $tags) ? self::DIRECTORY_TAG : array_key_first($tags);
        $directory = $directoryTag === null ? '' : (string) $tags[$directoryTag];

        if ($directory === '') {
            return null;
        }

        unset($tags[$directoryTag]);

        return Rrd::name($directory, [$metric->measurement, ...array_values($tags)]);
    }

    /**
     * The file a metric was kept in before its measurement was renamed, null when it only ever had one name.
     */
    public function legacyPath(MetricIdentity $metric): ?RrdPath
    {
        $hostname = (string) ($metric->tags[self::DIRECTORY_TAG] ?? '');
        $name = $this->legacyName($metric->measurement, $metric->tags);

        if ($hostname === '' || $name === null) {
            return null;
        }

        return Rrd::name($hostname, $name);
    }

    /**
     * @param  array<string, scalar>  $tags
     * @return list<scalar|null>|null
     */
    private function legacyName(string $measurement, array $tags): ?array
    {
        return match ($measurement) {
            'processors' => ['processor', $tags['processor_type'] ?? null, $tags['processor_index'] ?? null],
            default => null,
        };
    }
}
