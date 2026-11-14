<?php

namespace App\Data\TimeSeries;

/**
 * The values of one series, one per point. Each point covers an interval of stored values:
 * their average, and the lowest and highest of them. Null where there is no data.
 */
final readonly class SeriesValues
{
    /**
     * @param  list<float|null>  $average
     * @param  list<float|null>  $min
     * @param  list<float|null>  $max
     */
    public function __construct(
        public array $average,
        public array $min,
        public array $max,
    ) {
    }
}
