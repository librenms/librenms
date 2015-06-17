<?php
namespace Leaseweb\InfluxDB\Test;

use Leaseweb\InfluxDB\ResultSet;

class ResultSetTest extends \PHPUnit_Framework_TestCase
{
    /** @var ResultSet  $resultSet*/
    protected $resultSet;

    public function setUp()
    {
        $resultJsonExample = file_get_contents(dirname(__FILE__) . '/result.example.json');
        $this->resultSet = new ResultSet($resultJsonExample);
    }

    public function testGetPointsFromMeasurementName()
    {
        $measurementName = 'cpu_load_short';

        $points = $this->resultSet->getPoints($measurementName);

        $this->assertTrue(
            is_array($points)
        );
    }
}