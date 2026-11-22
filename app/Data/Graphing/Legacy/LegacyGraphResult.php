<?php

namespace App\Data\Graphing\Legacy;

use App\Data\Graphing\GraphOptions;
use LibreNMS\RRD\RrdPath;

/**
 * What a legacy graph include file defined
 */
final readonly class LegacyGraphResult
{
    /**
     * @param  array<int, string>  $rrdOptions  the graph elements: DEF, CDEF, LINE, AREA, GPRINT, etc.
     * @param  array<int, RrdPath>  $rrdFiles  the rrd files the graph reads
     */
    public function __construct(
        public array $rrdOptions,
        public GraphOptions $options,
        public string $title,
        public array $rrdFiles,
    ) {
    }
}
