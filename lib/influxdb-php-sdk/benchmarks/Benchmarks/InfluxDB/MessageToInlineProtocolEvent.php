<?php
namespace Corley\Benchmarks\InfluxDB;

use Athletic\AthleticEvent;

class MessageToInlineProtocolEvent extends AthleticEvent
{
    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithNoTags()
    {
        \InfluxDB\Adapter\message_to_inline_protocol(
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
        );
    }

    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithGlobalTags()
    {
        \InfluxDB\Adapter\message_to_inline_protocol(
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
        );
    }

    /**
     * @iterations 10000
     */
    public function convertMessageToInlineProtocolWithDifferentTagLevels()
    {
        \InfluxDB\Adapter\message_to_inline_protocol(
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
        );
    }
}
