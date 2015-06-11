<?php
namespace Corley\Benchmarks\InfluxDB;

use Athletic\AthleticEvent;
use InfluxDB\Adapter\UdpAdapter;
use InfluxDB\Options;

class MessageToInlineProtocolEvent extends AthleticEvent
{
    private $method;
    private $object;

    public function setUp()
    {
        $object = new UdpAdapter(new Options());
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod("serialize");
        $method->setAccessible(true);

        $this->method = $method;
        $this->object = $object;
    }

    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithNoTags()
    {
        $this->method->invokeArgs($this->object, [
            [
                "points" => [
                    [
                        "measurement" => "vm-serie",
                        "fields" => [
                            "cpu" => 18.12,
                            "free" => 712423,
                        ],
                    ],
                ]
            ]
        ]);
    }

    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithGlobalTags()
    {
        $this->method->invokeArgs($this->object, [
            [
                "tags" => [
                    "dc"  => "eu-west-1",
                ],
                "points" => [
                    [
                        "measurement" => "vm-serie",
                        "fields" => [
                            "cpu" => 18.12,
                            "free" => 712423,
                        ],
                    ],
                ]
            ]
        ]);
    }

    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithDifferentTagLevels()
    {
        $this->method->invokeArgs($this->object, [
            [
                "tags" => [
                    "dc"  => "eu-west-1",
                ],
                "points" => [
                    [
                        "measurement" => "vm-serie",
                        "tags" => [
                            "server"  => "tc12",
                        ],
                        "fields" => [
                            "cpu" => 18.12,
                            "free" => 712423,
                        ],
                    ],
                ]
            ]
        ]);
    }
}
