<?php

namespace LibreNMS\Interfaces\Data\Graphing;

use LibreNMS\Exceptions\GraphException;

interface RrdGraphInterface extends GraphInterface
{
    /**
     * The full rrdtool graph argument list
     *
     * @return array<int, string|int|float>
     *
     * @throws GraphException
     */
    public function getRrdCommandOptions(): array;
}
