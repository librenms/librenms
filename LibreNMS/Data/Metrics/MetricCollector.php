<?php

namespace LibreNMS\Data\Metrics;

/**
 * @template TModel of object
 */
class MetricCollector
{
    /**
     * @var array<MetricEntry<TModel>>
     */
    private array $entries = [];

    /**
     * @param  object  $model
     * @param  string  $metric
     * @param  float|int  $value
     * @return void
     */
    public function record(object $model, string $metric, float|int $value): void
    {
        $this->entries[] = new MetricEntry($model, $metric, $value);
    }

    /**
     * @return MetricEntry<TModel>[]
     */
    public function entries(): array
    {
        return $this->entries;
    }
}
