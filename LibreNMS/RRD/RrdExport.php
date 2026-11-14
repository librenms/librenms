<?php

namespace LibreNMS\RRD;

/**
 * Values exported from rrd files: one column per exported value, with a row for each time
 */
final readonly class RrdExport
{
    /**
     * @param  list<int>  $timestamps  unix time of each row
     * @param  array<string, list<float|null>>  $columns  the values by legend, null where there is no data
     */
    public function __construct(
        public array $timestamps,
        public array $columns,
    ) {
    }
}
