<?php

namespace App\Data\Graphing;

/**
 * A series of GraphData, its values line up with the timestamps.
 * Colors are CSS hex colors.
 */
readonly class GraphDataSeries
{
    /**
     * @param  list<float|null>  $avg  null where there is no data
     * @param  list<float|null>|null  $min  null when the same as avg, as it is when each point is a single stored value
     * @param  list<float|null>|null  $max  null when the same as avg
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $unit,
        public string $color,
        public ?string $fillColor,
        public ?string $maxFillColor,
        public bool $stacked,
        public bool $invert,
        public float $drawScale,
        public array $avg,
        public ?array $min,
        public ?array $max,
    ) {
    }
}
