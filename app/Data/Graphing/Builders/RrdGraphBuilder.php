<?php

namespace App\Data\Graphing\Builders;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Traits\ColorIterator;
use App\Data\TimeSeries\Rrd\RrdFileValidator;
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
        protected readonly RrdFileValidator $validator,
    ) {
        $this->colors('mixed');
    }

    public static function data(GraphDataInterface $data, ?RrdFileValidator $validator = null): static
    {
        return new static($data, $validator ?? resolve(RrdFileValidator::class));
    }

    /**
     * @return array<int, string>
     */
    abstract public function build(GraphParameters $params): array;

    /**
     * The rrd file of a series, null when it does not exist
     */
    protected function filename(DataSeries $series): ?string
    {
        return $this->validator->validate($series->metric);
    }

    /**
     * Define the average, min and max of a series as $id, {$id}min and {$id}max
     *
     * @return array<int, string>
     */
    protected function defs(string $id, string $filename, DataSeries $series): array
    {
        return [
            "DEF:$id=$filename:$series->field:AVERAGE",
            "DEF:{$id}min=$filename:$series->field:MIN",
            "DEF:{$id}max=$filename:$series->field:MAX",
        ];
    }
}
