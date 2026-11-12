<?php

namespace App\Data\Graphing;

use App\Data\TimeSeries\MetricIdentity;

readonly class DataSeries
{
    /**
     * @param  string  $field  the field of the metric to show
     * @param  string  $unit  shown after the values in the legend
     * @param  float  $scale  converts the stored value to the value of this series, for example 8 for bytes to bits
     */
    public function __construct(
        public MetricIdentity $metric,
        public string $field,
        public string $description,
        public string $unit = '',
        public float $scale = 1,
        public SeriesStyle $style = new SeriesStyle,
    ) {
    }
}
