<?php

namespace spec\InfluxDB\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use InfluxDB\Adapter\HttpAdapter;
use InfluxDB\Exception\InfluxAuthorizationException;
use InfluxDB\Exception\InfluxBadResponseException;
use InfluxDB\Exception\InfluxGeneralException;
use InfluxDB\Exception\InfluxNoSeriesException;
use InfluxDB\Exception\InfluxUnexpectedResponseException;
use InfluxDB\Options;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpAdapterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Adapter\HttpAdapter');
    }

    function let(Options $options, Client $client)
    {
        $options->getHttpSeriesEndpoint()->willReturn("localhost");
        $options->getHttpDatabaseEndpoint()->willReturn("localhost");
        $options->getUsername()->willReturn("one");
        $options->getPassword()->willReturn("two");
        $this->beConstructedWith($options, $client);
    }

    function it_should_send_data_via_post(Client $client)
    {
        $responseBody = ['key'=>'value'];
        $response = new Response(200,[], Stream::factory(json_encode($responseBody)));
        $client->post("localhost", [
            'auth' => ["one", "two"],
            'exceptions' => false,
            'body' => json_encode(['pippo'])
        ])->willReturn($response)
          ->shouldBeCalledTimes(1);
        $this->send(["pippo"])->shouldReturn($responseBody);
    }

    function it_should_query_data(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
                "query" => [
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(200,[],null));
        $this->query("select * from tcp.test")->shouldReturn(null);
    }

    function it_should_query_data_with_time_precision(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
                "query" => [
                    "time_precision" => "s",
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(200, [], null));
        $this->query("select * from tcp.test", "s")->shouldReturn(null);
    }

    function it_should_list_all_databases(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
            ]
        )->shouldBeCalledTimes(1)->willReturn(new Response(200, [], null));

        $this->getDatabases()->shouldReturn(null);
    }

    function it_should_create_a_new_database(Client $client, Options $options)
    {
        $client->post(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
                "body" => json_encode(["name" => "db_name"])
            ]
        )->shouldBeCalledTimes(1)->willReturn(new Response(201, [], null));

        $this->createDatabase("db_name")->shouldReturn(true);
    }

    function it_should_return_true_with_success(Client $client) {
        foreach ([201,204,299] as $code) {
            $client->post(Argument::any(), Argument::any(), Argument::any())->willReturn(new Response($code, [], null));

            $this->createDatabase("db_name")->shouldReturn(true);
        }
    }



    function it_should_throw_no_series_exception (Client $client)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
                "query" => [
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(HttpAdapter::STATUS_CODE_BAD_REQUEST,[], Stream::factory("Couldn't find series: tcp.test")));
        $this->shouldThrow(new InfluxNoSeriesException("Couldn't find series: tcp.test", HttpAdapter::STATUS_CODE_BAD_REQUEST))
            ->during("query", ["select * from tcp.test"]);
    }

    function it_should_throw_authorization_exception (Client $client)
    {
        $codes = [HttpAdapter::STATUS_CODE_UNAUTHORIZED, HttpAdapter::STATUS_CODE_FORBIDDEN];
        foreach ($codes as $code) {
            $client->get(
                "localhost",
                [
                    "auth" => ["one", "two"],
                    "exceptions" => false,
                    "query" => [
                        "q" => "select * from tcp.test",
                    ]
                ]
            )->willReturn(new Response($code,[], Stream::factory("Message")));
            $this->shouldThrow(new InfluxAuthorizationException("Message", $code))
                ->during("query", ["select * from tcp.test"]);
        }
    }

    function it_should_throw_general_exception (Client $client)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "exceptions" => false,
                "query" => [
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(409,[], Stream::factory("Message")));
        $this->shouldThrow(new InfluxGeneralException("Message", 409))
            ->during("query", ["select * from tcp.test"]);
    }

    function it_should_throw_general_exception_with_default_message (Client $client)
    {
        $client->get(Argument::any(), Argument::any())->willReturn(new Response(409));
        $this->shouldThrow(new InfluxGeneralException("Conflict", 409))
            ->during("query", ["select * from tcp.test"]);
    }

    function it_should_throw_bad_response_exception(Client $client)
    {
        $response = new Response(200,[], Stream::factory('bad response'));
        $client->post("localhost", [
            'auth' => ["one", "two"],
            'exceptions' => false,
            'body' => json_encode(['pippo'])
        ])->willReturn($response)
            ->shouldBeCalledTimes(1);
        $this->shouldThrow(new InfluxBadResponseException("Unable to parse JSON data: JSON_ERROR_SYNTAX - Syntax error, malformed JSON; Response is 'bad response'", 0))
            ->during("send", [["pippo"]]);
    }

    function it_should_throw_unexpected_response_exception (Client $client)
    {
        foreach ([0, 300, 500] as $code) {
            $client->get(Argument::any(), Argument::any())->willReturn(new Response($code, [], Stream::factory("Message")));
            $this->shouldThrow(new InfluxUnexpectedResponseException("Message", $code))
                ->during("query", ["select * from tcp.test"]);
        }
    }
}
