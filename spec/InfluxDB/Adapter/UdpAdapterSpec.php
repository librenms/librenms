<?php

namespace spec\InfluxDB\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use InfluxDB\Options;

class UdpAdapterSpec extends ObjectBehavior
{
    function let(Options $options)
    {
        $this->beConstructedWith($options);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Adapter\UdpAdapter');
    }

    function it_should_implement_adapter_interface()
    {
        $this->shouldImplement("InfluxDB\Adapter\AdapterInterface");
        $this->shouldImplement("InfluxDB\Adapter\ConnectableInterface");
    }
}
