<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB;


class ResultSet
{
    /**
     * @var string
     */
    protected $raw = '';

    /**
     * @param      $raw
     *
     * @param bool $throwExceptions
     */
    public function __construct($raw, $throwExceptions = true)
    {

    }
}