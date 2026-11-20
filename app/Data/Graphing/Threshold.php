<?php

namespace App\Data\Graphing;

/**
 * A constant reference line, such as an alert limit, in the units of the series
 */
final readonly class Threshold
{
    /**
     * @param  string  $color  hex without a leading #
     */
    public function __construct(
        public float $value,
        public string $label,
        public string $color,
    ) {
    }
}
