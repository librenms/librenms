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
    public function getPoints($metricName = '', array $tags = array())
    {
        $points = array();

        // todo: we are considering always have a metricName
        foreach ($this->parsedResults['results'] as $result) {

            foreach ($result['series'] as $serie) {
                if ($serie['measurement'] == $metricName) {

                    $points[] = $this->getPointsFromSerie($serie);
                }
            }
        }

        return $points;
    }

    /**
     * @param array $serie
     * @return array
     */
    private function getPointsFromSerie(array $serie)
    {
        return array_combine(
            $serie['columns'],
            array_shift($serie['values'])
        );
    }
}