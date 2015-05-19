<?php
namespace spec\InfluxDB;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use InfluxDB\Adapter\GuzzleAdapter;
use InfluxDB\Adapter\UdpAdapter;
use InfluxDB\Adapter\AdapterInterface;
use InfluxDb\Adapter\QueryableInterface;

class ClientSpec extends ObjectBehavior
{
    function let(\InfluxDB\Adapter\AdapterInterface $adapter)
    {
       $this->setAdapter($adapter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Client');
    }

    function it_should_send_data(AdapterInterface $adapter)
    {
        $adapter->send([[
            "name" => "video.search",
            "columns" => ["author", "title"],
            "points" => [
                ["Guccini", "Autogrill"]
            ]
        ]], false)->shouldBeCalledTimes(1);

        $this->mark("video.search", [
            "author" => "Guccini",
            "title" => "Autogrill"
        ]);
    }

    function it_should_send_data_with_time_precision(AdapterInterface $adapter)
    {
        $adapter->send([[
            "name" => "video.search",
            "columns" => ["time", "author", "title"],
            "points" => [
                ["1410591552", "Guccini", "Autogrill"]
            ]
        ]], "s")->shouldBeCalledTimes(1);

        $this->mark("video.search", [
            "time" => "1410591552",
            "author" => "Guccini",
            "title" => "Autogrill"
        ], "s");
    }

    function it_should_query_on_querable_adapter(GuzzleAdapter $adapter)
    {
        $this->setAdapter($adapter);
        $adapter->query("select * from tcp.test", false)->willReturn([]);

        $this->query("select * from tcp.test")->shouldReturn([]);
    }

    function it_should_query_with_time_precision(GuzzleAdapter $adapter)
    {
        $this->setAdapter($adapter);
        $adapter->query("select * from tcp.test", "s")->willReturn([]);

        $this->query("select * from tcp.test", "s")->shouldReturn([]);
    }

    function it_should_query_but_skip_invalid_time_precision(GuzzleAdapter $adapter)
    {
        $this->setAdapter($adapter);
        $adapter->query("select * from tcp.test", false)->willReturn([]);

        $this->query("select * from tcp.test", "r")->shouldReturn([]);
    }

    function it_should_thrown_an_exception_on_unquerable_adapter(UdpAdapter $adapter)
    {
        $this->setAdapter($adapter);

        $this->shouldThrow("\\BadMethodCallException")->duringQuery("select * from tcp.test");
    }
}
