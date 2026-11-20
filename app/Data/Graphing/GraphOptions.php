<?php

namespace App\Data\Graphing;

/**
 * Presentation options chosen by the graph (scale and axis), as opposed to GraphParameters which come from the request
 */
readonly class GraphOptions
{
    /**
     * @param  bool|null  $scaleRigid  defaults to rigid when neither scaleMin nor scaleMax is set
     * @param  string  $legendTitle  heading above the series labels
     * @param  int|null  $unitsExponent  0 shows axis and legend values without SI prefixes, such as m or k
     * @param  string|null  $leftAxisFormat  rrdtool only, prefer $axisUnit
     * @param  string|null  $rightAxis  rrdtool only, a right axis with $rightAxisLabel mirrors the left one
     * @param  string|null  $rightAxisLabel  shows a right axis with the same scale as the left one
     * @param  string|null  $axisUnit  shown after the axis values, which are then shown without SI prefixes
     * @param  string  $palette  the graph_colours palette for series without a color
     * @param  list<Threshold>  $thresholds  reference lines, such as alert limits
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
        public array $thresholds = [],
        public float $scaleMargin = 0,
        public ?string $axisUnit = null,
    ) {
    }
}
