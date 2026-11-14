<?php

namespace App\Graphs;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesSum;
use App\Data\TimeSeries\SeriesData;
use App\Graphs\Rrd\RrdGraphRenderer;
use App\Graphs\Rrd\RrdSeriesReader;
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
     * The series of the graph, independent of the datastore so the raw data can be fetched without drawing.
     * Sums add up series listed before them.
     *
     * @return array<string, DataSeries|SeriesSum>
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

    public function getOptions(): GraphOptions
    {
        return $this->options();
    }

    /**
     * Validates and authorizes first.
     *
     * @throws GraphException
     */
    public function getData(): SeriesData
    {
        return $this->attempt(function (): SeriesData {
            $data = app(RrdSeriesReader::class)->read($this->series(), $this->params->from, $this->params->to, $this->params->width);

            return $data->series === [] ? throw new NoGraphData('No Data files') : $data;
        });
    }

    /**
     * @return array<string, DataSeries|SeriesSum>
     *
     * @throws GraphException
     */
    private function series(): array
    {
        return $this->getSeries() ?: throw new NoGraphData;
    }
}
