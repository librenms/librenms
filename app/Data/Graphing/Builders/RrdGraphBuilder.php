<?php

namespace App\Data\Graphing\Builders;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\Rrd\RrdFileTracker;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;
use LibreNMS\Data\Store\Rrd;

/**
 * Builds the graph elements of an rrdtool graph from data series
 */
abstract class RrdGraphBuilder
{
    protected const PRECISION = 2;

    /** @var array<string, array{suffix: string, cf: string}> legend columns and the value each one shows */
    private const LEGEND_COLUMNS = [
        'Now' => ['suffix' => '', 'cf' => 'LAST'],
        'Min' => ['suffix' => 'min', 'cf' => 'MIN'],
        'Max' => ['suffix' => 'max', 'cf' => 'MAX'],
        'Avg' => ['suffix' => '', 'cf' => 'AVERAGE'],
    ];
    // a value is 5 digits and an SI prefix
    private const LEGEND_VALUE_WIDTH = 6;
    // rrdtool separates legend items with 2 spaces
    private const LEGEND_COLUMN_GAP = 2;
    // series labels start further right than the heading because of the color box
    private const LEGEND_COLOR_BOX_WIDTH = 2;

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
     * The legend heading: a title above the series labels and a label above each column of values
     */
    protected function legendHeader(string $title, int $labelWidth, string $units = ''): string
    {
        $header = Rrd::fixedSafeDescr($title, $labelWidth);
        $width = self::LEGEND_VALUE_WIDTH + strlen($units) + self::LEGEND_COLUMN_GAP + self::LEGEND_COLOR_BOX_WIDTH;

        foreach (array_keys(self::LEGEND_COLUMNS) as $label) {
            $header .= str_pad($label, $width, ' ', STR_PAD_LEFT);
            $width = self::LEGEND_VALUE_WIDTH + strlen($units) + self::LEGEND_COLUMN_GAP;
        }

        return "COMMENT:$header\\l";
    }

    /**
     * The legend values of a series defined by defs(), ending the legend line
     *
     * @return array<int, string>
     */
    protected function legendValues(string $id, string $units = ''): array
    {
        $values = [];

        foreach (self::LEGEND_COLUMNS as $column) {
            $values[] = "GPRINT:$id{$column['suffix']}:{$column['cf']}:%5." . self::PRECISION . "lf%s$units";
        }

        $values[array_key_last($values)] .= '\\n';

        return $values;
    }
}
