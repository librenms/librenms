<?php
namespace InfluxDB;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
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
     */
    public function testCreateTcpClient()
    {
        $options = [
            "adapter" => [
                "name" => "InfluxDB\\Adapter\\GuzzleAdapter",
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf("InfluxDB\\Adapter\\GuzzleAdapter", $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
    }
}
