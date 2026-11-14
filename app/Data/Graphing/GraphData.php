<?php

namespace App\Data\Graphing;

use App\Data\TimeSeries\SeriesData;

/**
 * A graph as data instead of an image: its series, how the graph wants them shown and their values
 */
final readonly class GraphData
{
    /**
     * @param  array<string, DataSeries>  $series  the series that have data, in the order they are drawn
     * @param  SeriesData  $data  the values of the series
     */
    public function __construct(
        public GraphOptions $options,
        public array $series,
        public SeriesData $data,
    ) {
    }
}
