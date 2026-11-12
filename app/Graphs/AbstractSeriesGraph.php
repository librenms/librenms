<?php

namespace App\Graphs;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Graphs\Rrd\RrdGraphRenderer;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph defined by its series and options, drawn by a renderer for the datastore
 */
abstract class AbstractSeriesGraph extends AbstractGraph
{
    protected function options(): GraphOptions
    {
        return new GraphOptions;
    }

    /**
     * The series of the graph, independent of the datastore so the raw data can be fetched without drawing
     *
     * @return array<string, DataSeries>
     */
    abstract public function getSeries(): array;

    /**
     * Draws the series from the datastore, override to draw them with another backend
     */
    protected function renderer(): RrdGraphRenderer
    {
        return app(RrdGraphRenderer::class, ['params' => $this->params]);
    }

    public function getDebugCommand(): ?string
    {
        $this->checkRenderable();

        return $this->renderer()->seriesDebugCommand($this->getTitle(), $this->series(), $this->options());
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
