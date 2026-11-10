<?php

namespace App\Data\Graphing;

use App\Data\Graphing\Rrd\RrdSeriesRenderer;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

/**
 * A graph defined by its series and options, drawn by a renderer for the datastore
 */
abstract class AbstractSeriesGraph extends AbstractGraph implements GraphDataInterface
{
    protected function options(): GraphOptions
    {
        return new GraphOptions;
    }

    /**
     * Draws the series from the datastore
     */
    protected function renderer(): RrdSeriesRenderer
    {
        return app(RrdSeriesRenderer::class, ['params' => $this->params]);
    }

    public function getDebugCommand(): ?string
    {
        $this->checkRenderable();

        return $this->renderer()->debugCommand($this->getTitle(), $this->series(), $this->options());
    }

    protected function draw(): GraphImage
    {
        return $this->renderer()->render($this->getTitle(), $this->series(), $this->options());
    }

    /**
     * @return array<string, DataSeries>
     *
     * @throws GraphException
     */
    private function series(): array
    {
        return $this->getSeries() ?: throw new NoGraphData;
    }
}
