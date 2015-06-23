<?php
namespace InfluxDB\Adater;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Client;
use Prophecy\Argument;

class GuzzleAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group tcp
     * @group proxy
     * @dataProvider getWriteEndpoints
     */
    public function testWriteEndpointGeneration($final, $options)
    {
        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new InfluxHttpAdapter($guzzleHttp, $options);

        $reflection = new \ReflectionClass(get_class($adapter));
        $method = $reflection->getMethod("getHttpSeriesEndpoint");
        $method->setAccessible(true);

        $endpoint = $method->invokeArgs($adapter, []);
        $this->assertEquals($final, $endpoint);
    }

    public function getWriteEndpoints()
    {
        return [
            ["http://localhost:9000/write", (new Options())->setHost("localhost")->setPort(9000)],
            ["https://localhost:9000/write", (new Options())->setHost("localhost")->setPort(9000)->setProtocol("https")],
            ["http://localhost:9000/influxdb/write", (new Options())->setHost("localhost")->setPort(9000)->setPrefix("/influxdb")],
        ];
    }

    /**
     * @group tcp
     * @group proxy
     * @dataProvider getQueryEndpoints
     */
    public function testQueryEndpointGeneration($final, $options)
    {
        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new InfluxHttpAdapter($guzzleHttp, $options);

        $reflection = new \ReflectionClass(get_class($adapter));
        $method = $reflection->getMethod("getHttpQueryEndpoint");
        $method->setAccessible(true);

        $endpoint = $method->invokeArgs($adapter, []);
        $this->assertEquals($final, $endpoint);
    }

    public function getQueryEndpoints()
    {
        return [
            ["http://localhost:9000/query", (new Options())->setHost("localhost")->setPort(9000)],
            ["https://localhost:9000/query", (new Options())->setHost("localhost")->setPort(9000)->setProtocol("https")],
            ["http://localhost:9000/influxdb/query", (new Options())->setHost("localhost")->setPort(9000)->setPrefix("/influxdb")],
        ];
    }

    public function testMergeWithDefaultOptions()
    {
        $options = new Options();
        $options->setDatabase("db");
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "body" => '{"database":"db","retentionPolicy":"default"}',
        ])->shouldBeCalledTimes(1);

        $adapter = new InfluxHttpAdapter($httpClient->reveal(), $options);
        $adapter->send([]);
    }

    public function testAdapterPrepareJsonDataCorrectly()
    {
        $guzzleHttp = $this->prophesize("GuzzleHttp\Client");
        $guzzleHttp->post("http://localhost:8086/write", [
            "auth" => ["root", "root"],
            "body" => '{"database":"db","retentionPolicy":"default","points":[{"measurement":"tcp.test","fields":{"mark":"element"}}]}',
        ])->shouldBeCalledTimes(1);
        $options = (new Options())->setDatabase("db");
        $adapter = new InfluxHttpAdapter($guzzleHttp->reveal(), $options);

        $adapter->send([
            "points" => [
                [
                    "measurement" => "tcp.test",
                    "fields" => [
                        "mark" => "element"
                    ]
                ]
            ]
        ]);
    }

    public function testDefaultOptionOverwrite()
    {
        $options = new Options();
        $options->setDatabase("db");
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "body" => '{"database":"mydb","retentionPolicy":"myPolicy"}',
        ])->shouldBeCalledTimes(1);

        $adapter = new InfluxHttpAdapter($httpClient->reveal(), $options);
        $adapter->send([
            "database" => "mydb",
            "retentionPolicy" => "myPolicy"
        ]);
    }

    public function testEmptyTagsFieldIsRemoved()
    {
        $options = new Options();
        $options->setDatabase("db");
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "body" => '{"database":"mydb","retentionPolicy":"myPolicy"}',
        ])->shouldBeCalledTimes(1);

        $adapter = new InfluxHttpAdapter($httpClient->reveal(), $options);
        $adapter->send([
            "database" => "mydb",
            "retentionPolicy" => "myPolicy",
            "tags" => [],
        ]);
    }

    public function testGlobalTagsAreInPlace()
    {
        $options = new Options();
        $options->setDatabase("db");
        $options->setTags([
            "dc" => "us-west",
        ]);
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "body" => '{"database":"mydb","retentionPolicy":"myPolicy","tags":{"dc":"us-west"}}',
        ])->shouldBeCalledTimes(1);

        $adapter = new InfluxHttpAdapter($httpClient->reveal(), $options);
        $adapter->send([
            "database" => "mydb",
            "retentionPolicy" => "myPolicy",
        ]);
    }

    public function testTagsFieldIsMergedWithGlobalTags()
    {
        $options = new Options();
        $options->setDatabase("db");
        $options->setTags([
            "dc" => "us-west",
        ]);
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "body" => '{"database":"mydb","retentionPolicy":"myPolicy","tags":{"dc":"us-west","region":"us"}}',
        ])->shouldBeCalledTimes(1);

        $adapter = new InfluxHttpAdapter($httpClient->reveal(), $options);
        $adapter->send([
            "database" => "mydb",
            "retentionPolicy" => "myPolicy",
            "tags" => ["region" => "us"],
        ]);
    }
}
