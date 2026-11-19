<?php

namespace App\Data\TimeSeries;

use LibreNMS\Interfaces\Models\HasMetrics;

/**
 * @template-contravariant TModel of HasMetrics
 */
class MetricCollector
{
    /**
     * @var list<MetricEntry>
     */
    private array $entries = [];

    /**
     * @param  string[]  $allowed  measurements that may be recorded, empty allows all
     */
    public function __construct(public readonly array $allowed = [])
    {
    }

    /**
     * @param  TModel  $model
     * @param  array<string, float|int>  $fields
     */
    public function record(HasMetrics $model, string $measurement, array $fields): void
    {
        if ($this->allowed && ! in_array($measurement, $this->allowed, true)) {
            throw new \RuntimeException('Invalid metric: ' . $measurement);
        }

        $this->entries[] = new MetricEntry($model->metricIdentity($measurement), $fields);
    }

    /**
     * @return list<MetricEntry>
     */
    public function entries(): array
    {
        return $this->entries;
    }
}
