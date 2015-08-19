<?php

namespace InfluxDB\Database;

/**
 * Class RetentionPolicy
 *
 * @package InfluxDB\Database
 * @author Stephen "TheCodeAssassin" Hoogendijk
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
        $this->name = (string) $name;
        $this->duration = $duration;
        $this->replication = (int) $replication;
        $this->default = (bool) $default;
    }
}
