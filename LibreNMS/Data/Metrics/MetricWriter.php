<?php

namespace LibreNMS\Data\Metrics;

use LibreNMS\Interfaces\Data\WriteInterface;

class MetricWriter
{
    public function __construct(
        private readonly MetricCollector $collector,
        private readonly WriteInterface $writer,
    ) {}

    public function writeMetrics(): void
    {
        foreach ($this->collector->metrics() as $metric) {
            $this->writeMetric($metric);
        }
    }

    public function writeMetric(string $metric, array $meta = []): void
    {
        foreach($this->collector->forMetric($metric) as $entry) {
            $meta['rrd_name'] = $metric . '-' . implode('-', $entry->tags);

            $this->writer->write($metric, $entry->fields, $entry->tags, $meta);
        }
    }
}
