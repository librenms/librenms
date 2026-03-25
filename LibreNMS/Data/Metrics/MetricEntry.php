<?php

namespace LibreNMS\Data\Metrics;

/**
 * @template TModel of object
 */
readonly class MetricEntry
{
    /**
     * @param  TModel  $model
     * @param  string  $metric
     * @param  float|int  $value
     */
    public function __construct(
        public object    $model,
        public string    $metric,
        public float|int $value,
    ) {}
}
