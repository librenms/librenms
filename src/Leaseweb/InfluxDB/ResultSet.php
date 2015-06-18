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

    /**
     * @var array|mixed
     */
    protected $parsedResults = array();

    /**
     * @param $raw
     *
     * @throws \InvalidArgumentException
     * @throws InfluxDBClientError
     */
    public function __construct($raw)
    {
        $this->raw = $raw;

        $this->parsedResults = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON");
        }

        // There was an error in the query thrown by influxdb
        if (isset($this->parsedResults['error'])) {
            throw new InfluxDBClientError($this->parsedResults['error']);
        }
    }

    /**
     * @param $metricName
     * @param array $tags
     *
     * @return array $points
     */
    public function getPoints($metricName = '', array $tags = array())
    {
        $points = array();

        foreach ($this->getSeries() as $serie) {

            if ((empty($metricName) && empty($tags))
                || $serie['name'] == $metricName
                || array_intersect($tags, $serie['tags'])
            ) {
                $points = array_merge($points, $this->getPointsFromSerie($serie));
            }
        }

        return $points;
    }

    /**
     * @see: https://influxdb.com/docs/v0.9/concepts/reading_and_writing_data.html
     *
     * results is an array of objects, one for each query,
     * each containing the keys for a series
     *
     * @throws InfluxDBClientError
     *
     * @return array $series
     */
    public function getSeries()
    {
        $pickSeries = function ($object) {
            
            if (isset($object['error'])) {
                throw new InfluxDBClientError($object['error']);
            }

            return $object['series'];
        };

        // Foreach object, pick series key
        return array_shift(
            array_map($pickSeries, $this->parsedResults['results'])
        );
    }

    /**
     * @param array $serie
     * @return array
     */
    private function getPointsFromSerie(array $serie)
    {
        $points = array();

        foreach ($serie['values'] as $point) {
            $points[] = array_combine(
                $serie['columns'],
                $point
            );
        }

        return $points;
    }

}