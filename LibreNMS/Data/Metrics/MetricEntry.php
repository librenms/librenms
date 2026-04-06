<?php

namespace LibreNMS\Data\Metrics;

readonly class MetricEntry
{
    /**
     * @param  string  $metric metric name
     * @param  array<string, float|int>  $fields
     * @param  array<string, string|int> $tags
     */
    public function __construct(
        public string $metric,
        public array $fields,
        public array $tags,
    ) {}
}
