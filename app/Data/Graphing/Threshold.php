<?php

namespace App\Data\Graphing;

/**
 * A constant reference line, such as an alert limit, in the units of the series
 */
final readonly class Threshold
{
    /**
     * @param  SeriesRole  $role  what the line marks, such as SeriesRole::HighLimit
     * @param  bool  $shown  drawn by default, otherwise only when the viewer turns it on
     * @param  bool  $invert  a limit of the series drawn below the axis, drawn below it too when they are
     * @param  bool  $mirror  a limit of the series on both sides of the axis, also drawn below it when series are
     */
    public function __construct(
        public float $value,
        public string $label,
        public SeriesRole $role,
        public bool $shown = true,
        public bool $invert = false,
        public bool $mirror = false,
    ) {
    }

    /**
     * Where the line is drawn, in the drawn values of the graph
     *
     * @param  bool  $inverted  series marked invert are drawn below the axis
     * @return list<float> one value, two for a mirrored line
     */
    public function drawnValues(bool $inverted): array
    {
        if (! $inverted) {
            return [$this->value];
        }

        if ($this->mirror) {
            return [$this->value, -$this->value];
        }

        return [$this->invert ? -$this->value : $this->value];
    }
}
