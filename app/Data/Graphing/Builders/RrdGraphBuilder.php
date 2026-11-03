<?php

namespace App\Data\Graphing\Builders;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\Rrd\RrdFileTracker;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;

/**
 * Builds the graph elements of an rrdtool graph from data series
 */
abstract class RrdGraphBuilder
{
    protected const PRECISION = 2;

    private string $colorPalette = 'mixed';
    private int $colorIndex = 0;
    /** @var array<string, SeriesStyle> */
    private array $styles = [];

    /**
     * @param  array<string, DataSeries>  $series
     */
    public function __construct(
        protected readonly array $series,
        private readonly RrdFileTracker $rrdFiles,
    ) {
    }

    /**
     * @return array<int, string>
     */
    abstract public function build(GraphParameters $params): array;

    public function colors(string $palette): static
    {
        $this->colorPalette = $palette;

        return $this;
    }

    /**
     * @param  string|string[]  $series  series keys
     */
    public function setSeriesStyle(string|array $series, SeriesStyle $style): static
    {
        foreach (Arr::wrap($series) as $key) {
            $this->styles[$key] = $style;
        }

        return $this;
    }

    protected function style(string $series): SeriesStyle
    {
        return $this->styles[$series] ?? new SeriesStyle;
    }

    protected function nextColor(?string $color = null): string
    {
        if ($color !== null) {
            return $color;
        }

        if (! LibrenmsConfig::has("graph_colours.$this->colorPalette.$this->colorIndex")) {
            $this->colorIndex = 0;
        }

        return LibrenmsConfig::get("graph_colours.$this->colorPalette." . $this->colorIndex++);
    }

    /**
     * The rrd file of a series, null when it does not exist
     */
    protected function filename(DataSeries $series): ?string
    {
        return $this->rrdFiles->pathFor($series->metric);
    }

    /**
     * Define the average, min and max of a series as $id, {$id}min and {$id}max
     *
     * @return array<int, string>
     */
    protected function defs(string $id, string $filename, DataSeries $series): array
    {
        return [
            "DEF:$id=$filename:$series->field:AVERAGE",
            "DEF:{$id}min=$filename:$series->field:MIN",
            "DEF:{$id}max=$filename:$series->field:MAX",
        ];
    }

    /**
     * Legend values of a series defined by defs(): now, min, max and average, ending the legend line
     *
     * @return array<int, string>
     */
    protected function legendValues(string $id, string $units = ''): array
    {
        $format = '%5.' . self::PRECISION . 'lf%s' . $units;

        return [
            "GPRINT:$id:LAST:$format",
            "GPRINT:{$id}min:MIN:$format",
            "GPRINT:{$id}max:MAX:$format",
            "GPRINT:$id:AVERAGE:$format\\n",
        ];
    }
}
