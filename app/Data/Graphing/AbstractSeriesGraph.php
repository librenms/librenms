<?php

namespace App\Data\Graphing;

use App\Data\Graphing\Rrd\RrdSeriesDefinition;
use App\Data\TimeSeries\Rrd\RrdFileResolver;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

/**
 * A graph defined by its series
 */
abstract class AbstractSeriesGraph extends AbstractRrdGraph implements GraphDataInterface
{
    protected function rrdDefinition(): array
    {
        $series = $this->getSeries();

        if (empty($series)) {
            throw new NoGraphData;
        }

        return (new RrdSeriesDefinition($series, $this->options(), app(RrdFileResolver::class)))->build($this->params);
    }
}
