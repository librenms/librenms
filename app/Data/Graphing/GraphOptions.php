<?php

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;

/**
 * Presentation options chosen by the graph (scale and axis), as opposed to GraphParameters which come from the request
 */
readonly class GraphOptions
{
    // of the spread of the thresholds, between them and the edge of the axis
    private const float THRESHOLD_ROOM = 0.05;

    /**
     * @param  bool|null  $scaleRigid  defaults to rigid when neither scaleMin nor scaleMax is set
     * @param  string  $legendTitle  heading above the series labels
     * @param  int|null  $unitsExponent  0 shows axis and legend values without SI prefixes, such as m or k
     * @param  string|null  $leftAxisFormat  rrdtool only, prefer $axisUnit
     * @param  string|null  $rightAxis  rrdtool only, a right axis with $rightAxisLabel mirrors the left one
     * @param  string|null  $rightAxisLabel  shows a right axis with the same scale as the left one
     * @param  string|null  $axisUnit  shown after the axis values, which are then shown without SI prefixes
     * @param  string  $palette  the graph_colours palette for series without a color
     * @param  list<int|float>  $percentiles  a legend column of each percentile for every series, series with SeriesStyle::$percentileLines also draw them
     * @param  list<Threshold>  $thresholds  reference lines, such as alert limits, those shown are always inside the value axis
     * @param  float  $scaleMargin  keep this fraction of the highest and lowest values of each line between them and the edge of an
     *                              autoscaled axis, so values that barely change still get a range with labels
     */
    public function __construct(
        public ?float $scaleMin = null,
        public ?float $scaleMax = null,
        public ?bool $scaleRigid = null,
        public ?int $base = null,
        public ?int $unitsExponent = null,
        public bool $logarithmic = false,
        public bool $altYGrid = false,
        public ?string $verticalLabel = null,
        public ?string $leftAxisFormat = null,
        public ?string $rightAxis = null,
        public ?string $rightAxisLabel = null,
        public ?int $unitsLength = null,
        public string $legendTitle = '',
        public string $palette = 'mixed',
        public ?TrafficOptions $traffic = null,
        public array $percentiles = [],
        public array $thresholds = [],
        public float $scaleMargin = 0,
        public ?string $axisUnit = null,
    ) {
    }

    /**
     * The percentiles of the legacy generic_stats graphs, none when graph_stat_percentile_disable is set
     *
     * @return list<int>
     */
    public static function statsPercentiles(): array
    {
        return self::showsPercentiles() ? [25, 50, 75] : [];
    }

    public static function showsPercentiles(): bool
    {
        return ! LibrenmsConfig::get('graph_stat_percentile_disable');
    }

    /**
     * @param  list<string>|null  $series  traffic series to summarize, all series when null
     * @param  list<Threshold>  $thresholds  reference lines for the traffic graph
     */
    public static function forTraffic(?array $series = null, bool $speedZoom = false, array $thresholds = []): self
    {
        $percentile = self::showsPercentiles() ? (float) LibrenmsConfig::get('percentile_value', 95) : null;

        return new self(
            legendTitle: 'bps',
            traffic: new TrafficOptions(
                highestPercentile: LibrenmsConfig::get('rrdgraph_real_percentile') ? $percentile : null,
                speedZoom: $speedZoom,
                series: $series,
            ),
            percentiles: $percentile === null ? [] : [$percentile],
            thresholds: $thresholds,
        );
    }

    /**
     * The lowest and highest values the value axis has to show so every shown threshold is inside it, away from its edges.
     * Room is a share of the spread of the thresholds and does not cross zero, except around a threshold at zero.
     * The browser applies the same rule to the thresholds the viewer shows, see thresholdBounds in resources/js/timeSeriesChart.js.
     *
     * @param  bool  $inverted  series marked invert, and their thresholds, are drawn below the axis
     * @return array{float, float}|null null without shown thresholds
     */
    public function thresholdBounds(bool $inverted = true): ?array
    {
        $values = array_merge(...array_map(fn (Threshold $threshold) => $threshold->drawnValues($inverted), $this->shownThresholds()));
        if ($values === []) {
            return null;
        }

        $low = min($values);
        $high = max($values);
        $room = (($high - $low) ?: abs($high) ?: 1) * self::THRESHOLD_ROOM;

        return [
            $low > 0 && $low - $room < 0 ? 0.0 : $low - $room,
            $high < 0 && $high + $room > 0 ? 0.0 : $high + $room,
        ];
    }

    /**
     * The thresholds drawn unless the viewer chooses otherwise
     *
     * @return list<Threshold>
     */
    public function shownThresholds(): array
    {
        return array_values(array_filter($this->thresholds, fn (Threshold $threshold) => $threshold->shown));
    }
}
