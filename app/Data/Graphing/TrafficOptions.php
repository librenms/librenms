<?php

namespace App\Data\Graphing;

/**
 * Traffic summaries and reference speeds, expressed in the units of the series.
 */
final readonly class TrafficOptions
{
    /**
     * @param  array<string, int>  $speeds  port speeds keyed by series name
     * @param  list<string>|null  $series  keys of the series to summarize, all series when null
     */
    public function __construct(
        public float $percentile = 95,
        public bool $highestPercentile = false,
        public float $totalScale = 0.125,
        public string $totalUnit = 'B',
        public array $speeds = [],
        public bool $speedZoom = false,
        public ?array $series = null,
    ) {
    }

    public function summarizes(string $key): bool
    {
        return $this->series === null || in_array($key, $this->series, true);
    }
}
