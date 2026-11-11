<?php

namespace App\Data\TimeSeries;

/**
 * The names a metric was stored under before its identity was cleaned up, see MetricNames
 */
final readonly class LegacyMetric
{
    /**
     * @param  array<string, string>  $tags  the name of each tag that was stored, by its name in the identity. The hostname was added by the datastores.
     * @param  list<string>|null  $rrdName  the parts of the rrd file name, {tag} is the value of a tag of the identity. Null when the file was named after the measurement.
     */
    public function __construct(
        public string $measurement,
        public array $tags = [],
        public ?array $rrdName = null,
    ) {
    }

    /**
     * @return array<string, scalar>
     */
    public function tagsOf(MetricIdentity $metric): array
    {
        $tags = [];
        foreach ($this->tags as $name => $legacyName) {
            if (isset($metric->tags[$name])) {
                $tags[$legacyName] = $metric->tags[$name];
            }
        }

        return $tags;
    }

    /**
     * @return list<string>|null null when the file was named after the measurement
     */
    public function rrdNameOf(MetricIdentity $metric): ?array
    {
        if ($this->rrdName === null) {
            return null;
        }

        $values = [];
        foreach ($metric->tags as $name => $value) {
            $values['{' . $name . '}'] = (string) $value;
        }

        return array_map(fn (string $part): string => strtr($part, $values), $this->rrdName);
    }
}
