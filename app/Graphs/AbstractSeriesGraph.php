<?php

namespace App\Graphs;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphData;
use App\Data\Graphing\GraphDataSeries;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesSum;
use App\Data\TimeSeries\SeriesData;
use App\Data\Graphing\ResolvedStyle;
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
                throw new NoGraphData('No Data files');
            }

            $options = $this->options();
            $styles = ResolvedStyle::resolve(array_intersect_key($series, $data->series), $options->palette);
            $css = fn (?string $hex) => $hex === null ? null : "#$hex";

            $drawn = [];
            foreach ($styles as $key => $style) {
                $drawn[] = new GraphDataSeries(
                    key: $key,
                    label: $series[$key]->description,
                    unit: $series[$key]->unit,
                    color: "#$style->color",
                    fillColor: $css($style->fillColor),
                    maxFillColor: $css($style->maxFillColor),
                    stacked: $style->stacked,
                    invert: $style->invert,
                    drawScale: $style->drawScale,
                    avg: $data->series[$key]['avg'],
                    min: $data->series[$key]['min'],
                    max: $data->series[$key]['max'],
                );
            }

            return new GraphData(
                timestamps: $data->timestamps,
                axis: [
                    'label' => $options->verticalLabel ?? $options->legendTitle,
                    'min' => $options->scaleMin ?? $this->params->scale_min,
                    'max' => $options->scaleMax ?? $this->params->scale_max,
                    'base' => $options->base ?? 1000,
                ],
                series: $drawn,
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
