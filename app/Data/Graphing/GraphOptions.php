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
     * @param  string  $palette  the graph_colours palette for series without a color
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
    ) {
    }
}
