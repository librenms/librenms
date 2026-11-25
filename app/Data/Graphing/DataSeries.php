<?php

namespace App\Data\Graphing;

use App\Data\TimeSeries\MetricIdentity;

readonly class DataSeries
{
    /**
     * @param  string|Formula  $field  the field of the metric to show, or a formula of its fields.
     *                                 The lowest and highest of a formula are those of its average values.
     * @param  string  $unit  shown after the values in the legend
     * @param  float  $scale  converts the stored value to the value of this series, for example 8 for bytes to bits
     * @param  float  $offset  added after the scale, for example 32 with a scale of 1.8 for celsius to fahrenheit
     * @param  string|null  $minField  the field holding the lowest value of each interval, instead of the lowest of $field
     * @param  string|null  $maxField  the field holding the highest value of each interval, instead of the highest of $field
     */
    public function __construct(
        public MetricIdentity $metric,
        public string|Formula $field,
        public string $description,
        public string $unit = '',
        public float $scale = 1,
        public SeriesStyle $style = new SeriesStyle,
        public float $offset = 0,
        public ?string $minField = null,
        public ?string $maxField = null,
    ) {
    }
}
