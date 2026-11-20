<?php

namespace App\Data\Graphing;

/**
 * A color with a variation for each site style, the renderer uses the variation of the style it draws in.
 * Colors are hex without a leading #.
 */
final readonly class ThemeColor
{
    public function __construct(
        public string $light,
        public string $dark,
    ) {
    }

    public function for(bool $dark): string
    {
        return $dark ? $this->dark : $this->light;
    }
}
