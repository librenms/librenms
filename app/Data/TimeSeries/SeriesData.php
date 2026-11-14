<?php

namespace App\Data\TimeSeries;

/**
 * The values of series over a time range, all series share the same points.
 * Each point covers an interval of stored values: their average, and the lowest and highest of them.
 * Series without stored data are left out, a value is null where there is no data.
 */
final readonly class SeriesData
{
    /**
     * @param  list<int>  $timestamps  unix time of the end of the interval each point covers
     * @param  array<string, array{avg: list<float|null>, min: list<float|null>, max: list<float|null>}>  $series  keyed like the requested series
     */
    public function __construct(
        public array $timestamps = [],
        public array $series = [],
    ) {
    }
}
