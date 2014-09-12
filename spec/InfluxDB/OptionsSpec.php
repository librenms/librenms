<?php

namespace spec\InfluxDB;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Options');
    }

    function it_should_create_a_valid_tcp_endpoint()
    {
        $this->setDatabase("mine");
        $this->getTcpEndpoint()
            ->shouldReturn("http://localhost:8086/db/mine/series?u=root&p=root");
    }

    function it_should_allows_option_override_for_tcp_endpoint()
    {
        $this->setHost("influx.1.prod.tld");
        $this->setPort(19385);
        $this->setUsername("walter");
        $this->setPassword("walter");
        $this->setDatabase("me");
        $this->getTcpEndpoint()
            ->shouldReturn("http://influx.1.prod.tld:19385/db/me/series?u=walter&p=walter");
    }

    function it_should_allows_https_for_tcp_endpoint()
    {
        $this->setProtocol("https");
        $this->setDatabase("me");
        $this->getTcpEndpoint()
            ->shouldReturn("https://localhost:8086/db/me/series?u=root&p=root");
    }
}
