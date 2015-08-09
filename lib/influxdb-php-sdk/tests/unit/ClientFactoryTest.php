<?php
namespace InfluxDB;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group factory
     *
     * @expectedException InvalidArgumentException
     */
    public function testEmptyOptions()
    {
        $client = ClientFactory::create([]);
    }

    /**
     * @group factory
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAdapter()
    {
        $client = ClientFactory::create(["adapter" => ["name" => "UdpAdapter"]]);
    }

    /**
     * @group factory
     * @group udp
     */
    public function testCreateUdpClient()
    {
        $options = [
            "adapter" => [
                "name" => "InfluxDB\\Adapter\\UdpAdapter",
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf("InfluxDB\\Adapter\\UdpAdapter", $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
    }

    /**
     * @group factory
     * @group tcp
     * @dataProvider getHttpAdapters
     */
    public function testCreateTcpClient($adapter)
    {
        $options = [
            "adapter" => [
                "name" => $adapter,
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf($adapter, $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
    }

    /**
     * @group factory
     * @dataProvider getHttpAdapters
     */
    public function testCreateTcpClientWithAllOptions($adapter)
    {
        $options = [
            "adapter" => [
                "name" => $adapter,
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
                "retention_policy" => "too_many_data",
                "tags" => [
                    "region" => "eu",
                    "env" => "prod",
                ],
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf($adapter, $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
        $this->assertEquals(["region" => "eu", "env" => "prod"], $client->getAdapter()->getOptions()->getTags());
        $this->assertEquals("too_many_data", $client->getAdapter()->getOptions()->getRetentionPolicy());
    }

    public function getHttpAdapters()
    {
        return [
            ["InfluxDB\\Adapter\\GuzzleAdapter"],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider getInvalidClasses
     */
    public function testInvalidProviderThrowsException($class)
    {
        $client = ClientFactory::create([
            "adapter" => [
                "name" => $class,
            ],
        ]);
    }

    public function getInvalidClasses()
    {
        return [["InvalidClass"],["stdClass"]];
    }
}
