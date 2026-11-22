<?php

namespace App\Data\Graphing;

/**
 * Traffic summaries, expressed in the units of the series.
 */
final readonly class TrafficOptions
{
    /**
     * @param  float|null  $highestPercentile  also show this percentile of the highest of the summarized series at each time, null for none
     * @param  bool  $speedZoom  zoomed to the port speed: series marked invert are drawn above the axis, over the others
     * @param  list<string>|null  $series  keys of the series to summarize, all series when null
     */
    public function __construct(
        public ?float $highestPercentile = null,
        public float $totalScale = 0.125,
        public string $totalUnit = 'B',
        public bool $speedZoom = false,
        public ?array $series = null,
    ) {
    }

    public function summarizes(string $key): bool
    {
        return $this->series === null || in_array($key, $this->series, true);
    }
}
