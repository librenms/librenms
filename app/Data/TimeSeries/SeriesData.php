<?php

namespace App\Data\TimeSeries;

/**
 * The values of series over a time range, all series share the same points
 */
final readonly class SeriesData
{
    /**
     * @param  list<int>  $timestamps  unix time of the end of the interval each point covers
     * @param  array<string, SeriesValues>  $series  keyed like the requested series, series without stored data are left out
     */
    public function __construct(
        public array $timestamps = [],
        public array $series = [],
    ) {
    }
}
