<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB;

/**
 * Class ResultSet
 *
 * @package Leaseweb\InfluxDB
 */
class ResultSet
{
    /**
     * @var string
     */
    protected $raw = '';

    protected $parsedResults = array();

    /**
     * @param $raw
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($raw)
    {
        $this->raw = $raw;

        $this->parsedResults = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON");
        }
    }

    /**
     * @param $metricName
     * @param array $tags
     *
     * @return array $points
     */
    public function getPoints($metricName = '', $tags = array())
    {
        return array();
    }
}