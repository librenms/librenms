<?php

namespace App\Graphs;

use App\Facades\LibrenmsConfig;

/**
 * Hands out the colors of a graph_colours palette in order, starting over when they run out
 */
class SeriesPalette
{
    private int $index = 0;

    public function __construct(private readonly string $name)
    {
    }

    /**
     * @return string hex color without a leading #
     */
    public function next(): string
    {
        if (! LibrenmsConfig::has("graph_colours.$this->name.$this->index")) {
            $this->index = 0;
        }

        return LibrenmsConfig::get("graph_colours.$this->name." . $this->index++);
    }
}
