<?php
namespace InfluxDB\Test;

use InfluxDB\ResultSet;
use PHPUnit\Framework\TestCase;

class ResultSetTest extends TestCase
{
    /** @var ResultSet  $resultSet*/
    protected $resultSet;

    public function setUp()
    {
        $resultJsonExample = file_get_contents(dirname(__FILE__) . '/json/result.example.json');
        $this->resultSet = new ResultSet($resultJsonExample);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionIfJSONisNotValid()
    {
        $invalidJSON = 'foo';

        new ResultSet($invalidJSON);
    }

    /**
     * Throws Exception if something went wrong with influxDB
     * @expectedException \InfluxDB\Exception
     */
    public function testThrowsInfluxDBException()
    {

        $errorResult = <<<EOD
{
    "series": [],
    "error": "Big error, many problems."
}
EOD;
        new ResultSet($errorResult);
    }

    /**
     * Throws Exception if something went wrong with influxDB after processing the query
     * @expectedException \InfluxDB\Exception
     */
    public function testThrowsInfluxDBExceptionIfAnyErrorInSeries()
    {

        $errorResult = <<<EOD
{
    "results": [
        {
            "series": [],
            "error": "There was an error after querying"
        }
    ]
}
EOD;
        new ResultSet($errorResult);
    }

    /**
     * We can get points from measurement
     */
    public function testGetPointsFromNameWithoudTags()
    {
        $resultJsonExample = file_get_contents(dirname(__FILE__) . '/json/result-no-tags.example.json');
        $this->resultSet = new ResultSet($resultJsonExample);

        $measurementName = 'cpu_load_short';
        $expectedNumberOfPoints = 2;

        $points = $this->resultSet->getPoints($measurementName);

        $this->assertTrue(is_array($points));

        $this->assertCount($expectedNumberOfPoints, $points);
    }

    /**
     * We can get points from measurement
     */
    public function testGetPoints()
    {
        $expectedNumberOfPoints = 3;

        $points = $this->resultSet->getPoints();

        $this->assertTrue(
            is_array($points)
        );

        $this->assertCount($expectedNumberOfPoints, $points);

    }

    public function testGetSeries()
    {
        $this->assertEquals(['time', 'value'], $this->resultSet->getColumns());
    }

    /**
     * We can get points from measurement
     */
    public function testGetPointsFromMeasurementName()
    {
        $measurementName = 'cpu_load_short';
        $expectedNumberOfPoints = 2;
        $expectedValueFromFirstPoint = 0.64;

        $points = $this->resultSet->getPoints($measurementName);

        $this->assertTrue(
            is_array($points)
        );

        $this->assertCount($expectedNumberOfPoints, $points);

        $somePoint = array_shift($points);

        $this->assertEquals($expectedValueFromFirstPoint, $somePoint['value']);
    }

    public function testGetPointsFromTags()
    {
        $tags = array("host" => "server01");
        $expectedNumberOfPoints = 2;

        $points = $this->resultSet->getPoints('', $tags);

        $this->assertTrue(is_array($points));
        $this->assertCount($expectedNumberOfPoints, $points);
    }

    public function testGetPointsFromNameAndTags()
    {
        $tags = array("host" => "server01");
        $expectedNumberOfPoints = 2;

        $points = $this->resultSet->getPoints('', $tags);

        $this->assertTrue(is_array($points));
        $this->assertCount($expectedNumberOfPoints, $points);
    }
}