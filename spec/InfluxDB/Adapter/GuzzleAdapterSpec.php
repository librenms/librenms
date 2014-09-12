<?php

namespace spec\InfluxDB\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GuzzleHttp\Client;
use InfluxDB\Options;

class GuzzleAdapterSpec extends ObjectBehavior
{
    function let(Client $client, Options $options)
    {
        $options->getTcpEndpoint()->willReturn("localhost");
        $this->beConstructedWith($client, $options);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Adapter\GuzzleAdapter');
    }

    function it_should_send_data_via_post(Client $client, Options $options)
    {
        $client->post("localhost", ['body' => json_encode(['pippo'])])->shouldBeCalledTimes(1);

        $this->send(["pippo"]);
    }
}
