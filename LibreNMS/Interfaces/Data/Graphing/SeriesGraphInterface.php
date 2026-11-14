<?php

namespace LibreNMS\Interfaces\Data\Graphing;

use App\Data\Graphing\GraphData;
use LibreNMS\Exceptions\GraphException;

/**
 * A graph that can also give the data it shows, to be drawn by the client or used without drawing
 */
interface SeriesGraphInterface extends GraphInterface
{
    /**
     * Validates input and authorization, then reads the data of the series
     *
     * @throws GraphException
     */
    public function getData(): GraphData;
}
