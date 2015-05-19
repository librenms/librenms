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
     * @dataProvider getTcpAdapters
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

    public function getTcpAdapters()
    {
        return [
            ["InfluxDB\\Adapter\\GuzzleAdapter"],
            ["InfluxDB\\Adapter\\HttpAdapter"],
        ];
    }

    /**
     * @group factory
     * @dataProvider getTcpAdapters
     */
    public function testCreateTcpClientWithFilter($adapter)
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
}
