<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Database;

/**
 * Class RetentionPolicy
 *
 * @package InfluxDB\Database
 */
class RetentionPolicy
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $duration;
    /**
     * @var int
     */
    public $replication;
    /**
     * @var bool
     */
    public $default;


    /**
     * @param string $name
     * @param string $duration
     * @param int    $replication
     * @param bool   $default
     *
     * @todo validate duration, replication
     */
    public function __construct($name, $duration = '1d', $replication = 1, $default = false)
    {
        $this->name = $name;
        $this->duration = $duration;
        $this->replication = $replication;

        $this->default = (bool) $default;
    }
}