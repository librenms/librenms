<?php

namespace InfluxDB;

use InfluxDB\Database\Exception as DatabaseException;

/**
 * Class Point
 *
 * @package InfluxDB
 */
class Point
{
    /**
     * @var string
     */
    private $measurement;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var string
     */
    private $timestamp;

    /**
     * The timestamp is optional. If you do not specify a timestamp the server’s
     * local timestamp will be used
     *
     * @param  string $measurement
     * @param  float  $value
     * @param  array  $tags
     * @param  array  $additionalFields
     * @param  null   $timestamp
     * @throws DatabaseException
     */
    public function __construct(
        $measurement,
        $value = null,
        array $tags = array(),
        array $additionalFields = array(),
        $timestamp = null
    ) {
        if (empty($measurement)) {
            throw new DatabaseException('Invalid measurement name provided');
        }

        $this->measurement = (string) $measurement;
        $this->tags = $tags;
        $this->fields = $additionalFields;

        if ($value) {
            $this->fields['value'] = $value;
        }

        if ($timestamp && !$this->isValidTimeStamp($timestamp)) {
            throw new DatabaseException(sprintf('%s is not a valid timestamp', $timestamp));
        }

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

        $string = $this->measurement;

        if (count($this->tags) > 0) {
            $string .=  ',' . $this->arrayToString($this->tags);
        }

        $string .= ' ' . $this->arrayToString($this->fields);

        if ($this->timestamp) {
            $string .= ' '.$this->timestamp;
        }

        return $string;
    }

    /**
     * @param  array $arr
     * @return string
     */
    private function arrayToString(array $arr)
    {
        $strParts = [];

        foreach ($arr as $key => $value) {
            $strParts[] = sprintf('%s=%s', $key, $value);
        }

        return implode(',', $strParts);
    }

    /**
     * @param  int $timestamp
     * @return bool
     */
    private function isValidTimeStamp($timestamp)
    {
        if ((int) $timestamp === $timestamp) {
            return true;
        }

        if ($timestamp <= PHP_INT_MAX && $timestamp >= ~PHP_INT_MAX) {
            return true;
        }

        return false;
    }
}
