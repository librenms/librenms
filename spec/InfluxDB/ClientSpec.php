<?php

namespace spec\InfluxDB;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
    
    function it_should_implement_client_interface()
    {
        $this->shouldImplement("InfluxDB\ClientInterface");
    }

    function it_should_send_data(\InfluxDB\Adapter\AdapterInterface $adapter)
    {
        $adapter->send([
            "name" => "video.search",
            "columns" => ["author", "title"],
            "points" => [
                ["Guccini", "Autogrill"]
            ] 
        ])->shouldBeCalledTimes(1);

        $this->mark("video.search", [
            "author" => "Guccini",
            "title" => "Autogrill"
        ]);  
    }
}
