<?php

namespace App\Data\Graphing;

readonly class SeriesStyle
{
    /**
     * @param  string|null  $color  defaults to the next color of the palette
     * @param  string|null  $areaColor  defaults to a transparent version of the line color
     */
    public function __construct(
        public ?string $color = null,
        public bool $area = false,
        public ?string $areaColor = null,
        public bool $invert = false,
    ) {
    }
}
