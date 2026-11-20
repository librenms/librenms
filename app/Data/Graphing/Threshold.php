<?php

namespace App\Data\Graphing;

/**
 * A constant reference line, such as an alert limit, in the units of the series
 */
final readonly class Threshold
{
    /**
     * @param  SeriesRole  $role  what the line marks, such as SeriesRole::HighLimit
     */
    public function __construct(
        public float $value,
        public string $label,
        public SeriesRole $role,
    ) {
    }
}
