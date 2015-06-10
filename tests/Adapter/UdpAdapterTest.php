<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

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
                "cpu value=1 1257894000"
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
                "cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12,free=712432 1257894000"
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
cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12 1257894000
mem,region=us-west,host=serverA,env=prod,target=servers,zone=1c free=712432 1257894000
EOF
            ],
        ];
    }
}
