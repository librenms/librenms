<?php

namespace LibreNMS\Interfaces\Data;

use App\Data\Graphing\DataSeries;
use App\Data\TimeSeries\SeriesData;

/**
 * Reads the stored values of series from a datastore, independent of how they are shown
 */
interface SeriesReaderInterface
{
    /**
     * Read the values of the series over a time range.
     * Longer ranges are consolidated so there are not many more than the requested number of points.
     *
     * @param  array<string, DataSeries>  $series
     * @param  int  $from  unix time
     * @param  int  $to  unix time
     * @param  int  $points  how many points are wanted, for example the width of a graph in pixels
     */
    public function read(array $series, int $from, int $to, int $points): SeriesData;
}
