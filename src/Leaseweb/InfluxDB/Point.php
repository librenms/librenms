<?php

namespace Leaseweb\InfluxDB;


class Point
{
    private $measurement;
    /**
     * @var array
     */
    private $tags;
    /**
     * @var array
     */
    private $fields;
    /**
     * @var string
     */
    private $timestamp;

    /**
     * @param $measurement
     * @param array $tags
     * @param array $fields
     * @param string $timestamp
     */
    public function __construct($measurement, array $tags, array $fields, $timestamp = '')
    {
        $this->measurement = $measurement;
        $this->tags = $tags;
        $this->fields = $fields;
        $this->timestamp = $timestamp;
    }

    /**
     * @see: https://influxdb.com/docs/v0.9/concepts/reading_and_writing_data.html
     *
     * Should return this format
     * 'cpu_load_short,host=server01,region=us-west value=0.64 1434055562000000000'
     */
    public function __toString()
    {
        return "";
        /*return sprintf(
            '%s,%s %s %s',
            $this->measurement,
            implode()
        );*/
    }

    public function a(){

    }
}