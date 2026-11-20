<?php

namespace App\Data\Graphing;

/**
 * The sum of other series of the graph, such as the traffic of all ports of a device.
 * Series without a value at a time count as zero, the sum has no value when none of them do.
 * The lowest and highest of a sum are those of its average values.
 */
readonly class SeriesSum
{
    /**
     * @param  list<string>  $series  keys of the series to add up, listed before this one in the graph
     * @param  string  $unit  shown after the values in the legend
     */
    public function __construct(
        public array $series,
        public string $description,
        public string $unit = '',
        public SeriesStyle $style = new SeriesStyle,
    ) {
    }
}
