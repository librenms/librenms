<?php

namespace App\Data\TimeSeries;

readonly class MetricEntry
{
    /**
     * @param  array<string, float|int>  $fields
     */
    public function __construct(
        public MetricIdentity $identity,
        public array $fields,
    ) {
    }
}
