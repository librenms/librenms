<?php
namespace InfluxDB\Adapter;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Client;
use Prophecy\Argument;

class UdpAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getMessages
     */
    public function testRewriteMessages($input, $response)
    {
        $object = new UdpAdapter(new Options());
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod("serialize");
        $method->setAccessible(true);

        $message = $method->invokeArgs($object, [$input]);

        $this->assertEquals($response, $message);
    }

    public function getMessages()
    {
        return [
            [
                [
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "value" => 1,
                            ],
                        ],
                    ],
                ],
                "cpu value=1 1257894000000000000"
            ],
            [
                [
                    "tags" => [
                        "region" => "us-west",
                        "host" => "serverA",
                        "env" => "prod",
                        "target" => "servers",
                        "zone" => "1c",
                    ],
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "cpu" => 18.12,
                                "free" => 712432,
                            ],
                        ],
                    ],
                ],
                "cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12,free=712432 1257894000000000000"
            ],
            [
                [
                    "tags" => [
                        "region" => "us-west",
                        "host" => "serverA",
                        "env" => "prod",
                        "target" => "servers",
                        "zone" => "1c",
                    ],
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "cpu" => 18.12,
                            ],
                        ],
                        [
                            "measurement" => "mem",
                            "fields" => [
                                "free" => 712432,
                            ],
                        ],
                    ],
                ],
                <<<EOF
cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12 1257894000000000000
mem,region=us-west,host=serverA,env=prod,target=servers,zone=1c free=712432 1257894000000000000
EOF
            ],
        ];
    }

    /**
     * @group udp
     */
    public function testUdpIpWriteDataWillBeConvertedAsLineProtocol()
    {
        $options = (new Options())->setDatabase("test");
        $adapter = $this->getMockBuilder("InfluxDB\\Adapter\\UdpAdapter")
            ->setConstructorArgs([$options])
            ->setMethods(["write", "generateTimeInNanoSeconds"])
            ->getMock();

        $adapter->expects($this->any())
            ->method("generateTimeInNanoSeconds")
            ->will($this->returnValue(1245));

        $adapter->expects($this->once())
            ->method("write")
            ->with("udp.test mark=\"element\" 1245");

        $adapter->send([
            "points" => [
                [
                    "measurement" => "udp.test",
                    "fields" => [
                        "mark" => "element"
                    ]
                ]
            ]
        ]);
    }

    /**
     * @group udp
     */
    public function testSendMultipleMeasurementWithUdpIp()
    {
        $options = (new Options())->setDatabase("test");
        $adapter = $this->getMockBuilder("InfluxDB\\Adapter\\UdpAdapter")
            ->setConstructorArgs([$options])
            ->setMethods(["write", "generateTimeInNanoSeconds"])
            ->getMock();

        $adapter->expects($this->any())
            ->method("generateTimeInNanoSeconds")
            ->will($this->onConsecutiveCalls(1245, 1246));

        $adapter->expects($this->once())
            ->method("write")
            ->with(<<<EOF
mem free=712423 1245
cpu cpu=18.12 1245
EOF
);

        $adapter->send([
            "points" => [
                [
                    "measurement" => "mem",
                    "fields" => [
                        "free" => 712423,
                    ],
                ],
                [
                    "measurement" => "cpu",
                    "fields" => [
                        "cpu" => 18.12,
                    ],
                ],
            ]
        ]);
    }

    /**
     * @group udp
     */
    public function testMergeGlobalTags()
    {
        $options = (new Options())
            ->setDatabase("test")
            ->setTags(["dc" => "eu-west"]);
        $adapter = $this->getMockBuilder("InfluxDB\\Adapter\\UdpAdapter")
            ->setConstructorArgs([$options])
            ->setMethods(["write", "generateTimeInNanoSeconds"])
            ->getMock();

        $adapter->expects($this->any())
            ->method("generateTimeInNanoSeconds")
            ->will($this->returnValue(1245));

        $adapter->expects($this->once())
            ->method("write")
            ->with(<<<EOF
mem,dc=eu-west,region=eu-west-1 free=712423 1245
EOF
);

        $adapter->send([
            "tags" => [
                "region" => "eu-west-1",
            ],
            "points" => [
                [
                    "measurement" => "mem",
                    "fields" => [
                        "free" => 712423,
                    ],
                ],
            ]
        ]);
    }

    /**
     * @group udp
     */
    public function testMergeFullTagsPositions()
    {
        $options = (new Options())
            ->setDatabase("test")
            ->setTags(["dc" => "eu-west"]);
        $adapter = $this->getMockBuilder("InfluxDB\\Adapter\\UdpAdapter")
            ->setConstructorArgs([$options])
            ->setMethods(["write", "generateTimeInNanoSeconds"])
            ->getMock();

        $adapter->expects($this->any())
            ->method("generateTimeInNanoSeconds")
            ->will($this->returnValue(1245));

        $adapter->expects($this->once())
            ->method("write")
            ->with(<<<EOF
mem,dc=eu-west,region=eu-west-1,location=ireland free=712423 1245
EOF
);

        $adapter->send([
            "tags" => [
                "region" => "eu-west-1",
            ],
            "points" => [
                [
                    "measurement" => "mem",
                    "tags" => [
                        "location" => "ireland",
                    ],
                    "fields" => [
                        "free" => 712423,
                    ],
                ],
            ]
        ]);
    }
}
