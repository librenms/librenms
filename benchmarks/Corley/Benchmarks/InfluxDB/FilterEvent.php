<?php
namespace Corley\Benchmarks\InfluxDB;

use Athletic\AthleticEvent;
use InfluxDB\Client;
use InfluxDB\Filter\ColumnsPointsFilter;
use Prophecy\Prophet;
use Prophecy\Argument;

class FilterEvent extends AthleticEvent
{
    private $withFilter;
    private $withoutFilter;
    private $testData;

    public function setUp()
    {
        $this->testData = [
            (object)[
                "name" => "test",
                "columns" => [
                    "time",
                    "sequence_number",
                    "value",
                ],
                "points" => [
                ],
            ]
        ];

        $prophet = new Prophet;
        $adapter = $prophet->prophesize('InfluxDB\Adapter\GuzzleAdapter');
        $adapter->query(Argument::any(), Argument::Any())->willReturn($this->testData);

        $this->withFilter = new Client();
        $this->withFilter->setAdapter($adapter->reveal());
        $this->withFilter->setFilter(new ColumnsPointsFilter());

        $this->withoutFilter = new Client();
        $this->withoutFilter->setAdapter($adapter->reveal());
    }

    /**
     * @iterations 10000
     */
    public function get10PointDirectData()
    {
        for ($i=0; $i<10; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }

        $this->withoutFilter->query("THE QUERY", "s");
    }

    /**
     * @iterations 10000
     */
    public function get10PointFilteredData()
    {
        for ($i=0; $i<10; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }
        $this->withFilter->query("THE QUERY", "s");
    }

    /**
     * @iterations 1000
     */
    public function get100PointDirectData()
    {
        for ($i=0; $i<100; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }

        $this->withoutFilter->query("THE QUERY", "s");
    }

    /**
     * @iterations 1000
     */
    public function get100PointFilteredData()
    {
        for ($i=0; $i<100; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }
        $this->withFilter->query("THE QUERY", "s");
    }

    /**
     * @iterations 100
     */
    public function get1000PointDirectData()
    {
        for ($i=0; $i<1000; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }

        $this->withoutFilter->query("THE QUERY", "s");
    }

    /**
     * @iterations 100
     */
    public function get1000PointFilteredData()
    {
        for ($i=0; $i<1000; $i++) {
            $this->testData[0]->points[] = [1985718957, 12519287519, 12589175198];
        }
        $this->withFilter->query("THE QUERY", "s");
    }
}
