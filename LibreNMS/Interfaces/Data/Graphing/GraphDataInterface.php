<?php

namespace LibreNMS\Interfaces\Data\Graphing;

use App\Data\Graphing\DataSeries;

/**
 * Graphs whose data is described by backend independent series,
 * so the raw data can be fetched without drawing the graph.
 */
interface GraphDataInterface
{
    /**
     * @return array<string, DataSeries>
     */
    public function getSeries(): array;
}
