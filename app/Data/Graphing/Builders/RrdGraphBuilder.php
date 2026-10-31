<?php

namespace App\Data\Graphing\Builders;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Traits\ColorIterator;
use App\Data\TimeSeries\Contracts\MetricValidator;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

/**
 * Builds rrdtool graph elements for the series of a graph
 */
abstract class RrdGraphBuilder
{
    use ColorIterator;

    protected const PRECISION = 2;

    final public function __construct(
        protected readonly GraphDataInterface $data,
        protected readonly MetricValidator $validator,
    ) {
        $this->colors('mixed');
    }

    public static function data(GraphDataInterface $data, ?MetricValidator $validator = null): static
    {
        return new static($data, $validator ?? resolve(MetricValidator::class));
    }

    /**
     * @return array<int, string>
     */
    abstract public function build(GraphParameters $params): array;

    /**
     * Define the average, min and max of a series as $id, {$id}min and {$id}max
     *
     * @return array<int, string>|null null when the series data does not exist
     */
    protected function defs(string $id, DataSeries $series): ?array
    {
        $filename = $this->validator->validate($series->metric);

        if ($filename === null) {
            return null;
        }

        return [
            "DEF:$id=$filename:$series->field:AVERAGE",
            "DEF:{$id}min=$filename:$series->field:MIN",
            "DEF:{$id}max=$filename:$series->field:MAX",
        ];
    }
}
