<?php

namespace InfluxDB\Filter;

class ColumnsPointsFilter implements FilterInterface
{
    public function filter($metrics)
    {
        $response = [];

        foreach ($metrics as $metric) {
            $columns = $metric->columns;
            $response[$metric->name] = [];

            foreach ($metric->points as $point) {
                $response[$metric->name][] = array_combine($columns, $point);
            }
        }

        return $response;
    }
}
