<?php

namespace App\Data\Graphing\Traits;

use App\Facades\LibrenmsConfig;

trait ColorIterator
{
    private int $colorIterator = 0;
    private string $colorPalette = 'default';

    public function colors(string $colors): self
    {
        $this->colorPalette = $colors;

        return $this;
    }

    protected function nextColor(?string $color = null): string
    {
        if ($color !== null) {
            return $color;
        }

        if (! LibrenmsConfig::has("graph_colours.$this->colorPalette.$this->colorIterator")) {
            $this->colorIterator = 0;
        }

        $color = LibrenmsConfig::get("graph_colours.$this->colorPalette.$this->colorIterator");
        $this->colorIterator++;

        return $color;
    }
}
