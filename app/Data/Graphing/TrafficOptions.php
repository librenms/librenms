<?php

namespace App\Data\Graphing;

/**
 * Traffic summaries and reference speeds, expressed in the units of the series.
 */
final readonly class TrafficOptions
{
    /** @param array<string, int> $speeds port speeds keyed by series name */
    public function __construct(
        public float $percentile = 95,
        public bool $highestPercentile = false,
        public float $totalScale = 0.125,
        public string $totalUnit = 'B',
        public array $speeds = [],
        public bool $speedZoom = false,
    ) {
    }
}
