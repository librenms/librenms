<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB\Database;


class RetentionPolicy
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $duration;
    /**
     * @var int
     */
    private $replication;
    /**
     * @var bool
     */
    private $default;


    /**
     * @param string $name
     * @param string $duration
     * @param int    $replication
     * @param bool   $default
     */
    public function __construct($name, $duration = '1d', $replication = 1, $default = false)
    {


        $this->name = $name;
        $this->duration = $duration;
        $this->replication = $replication;
        $this->default = $default;
    }
}