<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

use InfluxDB\ResultSet;

/**
 * Interface QueryDriverInterface
 *
 * @package InfluxDB\Driver
 */
interface QueryDriverInterface
{

    /**
     * @return ResultSet
     */
    public function query();
}