<?php

namespace LibreNMS\Data\Metrics;

use LibreNMS\Interfaces\Models\Keyable;

class MetricCollector
{
    /**
     * @var array<string, MetricEntry[]>
     */
    private array $entries = [];

    public function __construct(public readonly array $allowed = [])
    {
    }

    public function record(Keyable $model, string $metric, array $fields): void
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
