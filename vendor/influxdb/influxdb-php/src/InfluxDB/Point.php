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
    protected $measurement;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $timestamp;

    /**
     * The timestamp is optional. If you do not specify a timestamp the server’s
     * local timestamp will be used
     *
     * @param  string $measurement
     * @param  float  $value
     * @param  array  $tags
     * @param  array  $additionalFields
     * @param  int    $timestamp
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
        $this->setTags($tags);
        $fields = $additionalFields;

        if ($value !== null) {
            $fields['value'] = $value;
        }

        $this->setFields($fields);

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
            $string .=  ',' . $this->arrayToString($this->escapeCharacters($this->tags, true));
        }

        $string .= ' ' . $this->arrayToString($this->escapeCharacters($this->fields, false));

        if ($this->timestamp) {
            $string .= ' '.$this->timestamp;
        }

        return $string;
    }

    /**
     * @return string
     */
    public function getMeasurement()
    {
        return $this->measurement;
    }

    /**
     * @param string $measurement
     */
    public function setMeasurement($measurement)
    {
        $this->measurement = $measurement;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        foreach ($tags as &$tag) {
            if ($tag === '') {
                $tag = '""';
            } elseif (is_bool($tag)) {
                $tag = ($tag ? "true" : "false");
            } elseif (is_null($tag)) {
                $tag = ("null");
            }
        }
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        foreach ($fields as &$field) {
            if (is_integer($field)) {
                $field = sprintf('%di', $field);
            } elseif (is_string($field)) {
                $field = $this->escapeFieldValue($field);
            } elseif (is_bool($field)) {
                $field = ($field ? "true" : "false");
            } elseif (is_null($field)) {
                $field = $this->escapeFieldValue("null");
            }
        }

        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Escapes invalid characters in both the array key and optionally the array value
     *
     * @param array $arr
     * @param bool $escapeValues
     * @return array
     */
    private function escapeCharacters(array $arr, $escapeValues)
    {
        $returnArr = [];

        foreach ($arr as $key => $value) {
            $returnArr[$this->addSlashes($key)] = $escapeValues ? $this->addSlashes($value) : $value;
        }

        return $returnArr;
    }

    /*
     * Returns string double-quoted and double-quotes escaped per Influx write protocol syntax
     *
     * @param string $value
     * @return string
     */
    private function escapeFieldValue($value)
    {
        $escapedValue = str_replace('"', '\"', $value);
        return sprintf('"%s"', $escapedValue);
    }

    /**
     * Returns strings with space, comma, or equals sign characters backslashed per Influx write protocol syntax
     *
     * @param string $value
     * @return string
     */
    private function addSlashes($value)
    {
        return str_replace([
            ' ',
            ',',
            '='
        ],[
            '\ ',
            '\,',
            '\='
        ], $value);
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
        // if the code is run on a 32bit system, loosely check if the timestamp is a valid numeric
        if (PHP_INT_SIZE == 4 && is_numeric($timestamp)) {
            return true;
        }

        if (!is_numeric($timestamp)) {
            return false;
        }

        if (intval($timestamp) != $timestamp) {
            return false;
        }

        if (!($timestamp <= PHP_INT_MAX && $timestamp >= ~PHP_INT_MAX)) {
            return false;
        }

        return true;
    }
}
