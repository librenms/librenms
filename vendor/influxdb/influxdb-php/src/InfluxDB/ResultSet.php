<?php

namespace InfluxDB;

use InfluxDB\Client\Exception as ClientException;

/**
 * Class ResultSet
 *
 * @package InfluxDB
 * @author  Stephen "TheCodeAssassin" Hoogendijk
 */
class ResultSet
{
    /**
     * @var array|mixed
     */
    protected $parsedResults = [];

    /**
     * @var string
     */
    protected $rawResults = '';

    /**
     * @param string $raw
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function __construct($raw)
    {
        $this->rawResults = $raw;
        $this->parsedResults = json_decode((string) $raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON');
        }

        // There was an error in the query thrown by influxdb
        if (isset($this->parsedResults['error'])) {
            throw new ClientException($this->parsedResults['error']);
        }

        // Check if there are errors in the first serie
        if (isset($this->parsedResults['results'][0]['error'])) {
            throw new ClientException($this->parsedResults['results'][0]['error']);
        }
    }

    /**
     * @return string
     */
    public function getRaw() {
      return $this->rawResults;
    }

    /**
     * @param  $metricName
     * @param  array $tags
     * @return array $points
     */
    public function getPoints($metricName = '', array $tags = array())
    {
        $points = [];
        $series = $this->getSeries();

        foreach ($series as $serie) {
            if ((empty($metricName) && empty($tags)
                || $serie['name'] == $metricName
                || (isset($serie['tags']) && array_intersect($tags, $serie['tags'])))
                && isset($serie['values'])
            ) {
                foreach ($this->getPointsFromSerie($serie) as $point) {
                    $points[] = $point;
                }
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
     * @throws Exception
     * @return array $series
     */
    public function getSeries()
    {
        $series = array_map(
            function ($object) {
                if (isset($object['error'])) {
                    throw new ClientException($object['error']);
                }

                return isset($object['series']) ? $object['series'] : [];
            },
            $this->parsedResults['results']
        );

        return array_shift($series);
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->getSeries()[0]['columns'];
    }

    /**
     * @param  array $serie
     * @return array
     */
    private function getPointsFromSerie(array $serie)
    {
        $points = [];

        foreach ($serie['values'] as $point) {
            $points[] = array_combine($serie['columns'], $point);
        }

        return $points;
    }
}
