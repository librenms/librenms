<?php

namespace spec\InfluxDB\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UdpAdapterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Adapter\UdpAdapter');
    }

    function it_should_implement_adapter_interface()
    {
        $this->shouldImplement("InfluxDB\Adapter\AdapterInterface");
    }
}
