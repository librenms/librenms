<?php

namespace App\Graphs;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphData;
use App\Data\Graphing\GraphDataSeries;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesSum;
use App\Data\Graphing\Threshold;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
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

    /**
     * The values of each series and how to draw them, for drawing in the browser. Validates and authorizes first.
     *
     * @throws GraphException
     */
    public function getData(): GraphData
    {
        return $this->attempt(function (): GraphData {
            $series = $this->series();
            $data = app(RrdSeriesReader::class)->read($series, $this->params->from, $this->params->to, $this->params->width);

            if ($data->series === []) {
                throw new NoGraphData(__('graph.errors.no_data_files'));
            }

            $options = $this->options();
            // the browser makes overlapping series see through itself
            $theme = new GraphTheme($this->params->isDark(), $options->palette);
            $invert = ! LibrenmsConfig::get('webui.graph_stacked');

            return new GraphData(
                timestamps: $data->timestamps,
                axis: [
                    'label' => $options->verticalLabel ?? $options->legendTitle,
                    'min' => $options->scaleMin ?? $this->params->scale_min,
                    'max' => $options->scaleMax ?? $this->params->scale_max,
                    'base' => $options->base ?? 1000,
                    'margin' => $options->scaleMargin,
                ],
                series: GraphDataSeries::fromReadings($series, $data, $theme, $invert, $this->params->to - $this->params->from),
                thresholds: array_map(fn (Threshold $threshold) => [
                    'value' => $threshold->value,
                    'label' => $threshold->label,
                    'color' => '#' . $theme->color($threshold->role),
                    'shown' => $threshold->shown,
                    // as series marked invert, which stacked graphs draw above the axis
                    'invert' => $threshold->invert && $invert,
                    'mirror' => $threshold->mirror && $invert,
                ], $options->thresholds),
                percentiles: $options->percentiles,
            );
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
