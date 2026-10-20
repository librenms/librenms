<?php

namespace LibreNMS\Data\Metrics;

use LibreNMS\Interfaces\Models\HasMetricTags;

/**
 * @template-contravariant TModel of HasMetricTags
 */
class MetricCollector
{
    /**
     * @var array<string, MetricEntry[]>
     */
    private array $entries = [];

    /**
     * @param  string[]  $allowed  metric names that may be recorded, empty allows all
     */
    public function __construct(public readonly array $allowed = [])
    {
    }

    /**
     * @param  TModel  $model
     * @param  array<string, float|int>  $fields
     */
    public function record(HasMetricTags $model, string $metric, array $fields): void
    {
        if ($this->allowed && ! in_array($metric, $this->allowed)) {
            throw new \RuntimeException('Invalid metric: ' . $metric);
        }

        $this->entries[$metric][] = new MetricEntry($metric, $fields, $model->tags());
    }

    /**
     * @return \Generator<MetricEntry>
     */
    public function entries(): \Generator
    {
        foreach ($this->entries as $metricGroup) {
            yield from $metricGroup;
        }
    }

    /**
     * @param  string  $metric
     * @return MetricEntry[]
     */
    public function forMetric(string $metric): array
    {
        return $this->entries[$metric] ?? [];
    }

    /**
     * @return string[]
     */
    public function metrics(): array
    {
        return array_keys($this->entries);
    }
}
