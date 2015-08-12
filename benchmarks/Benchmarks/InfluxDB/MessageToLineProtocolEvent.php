<?php
namespace Corley\Benchmarks\InfluxDB;

use Athletic\AthleticEvent;

class MessageToLineProtocolEvent extends AthleticEvent
{
    /**
     * @iterations 10000
     */
    public function convertMessageToLineProtocolWithNoTags()
    {
        \InfluxDB\Adapter\message_to_line_protocol(
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
    public function convertMessageToLineProtocolWithGlobalTags()
    {
        \InfluxDB\Adapter\message_to_line_protocol(
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
    public function convertMessageToLineProtocolWithDifferentTagLevels()
    {
        \InfluxDB\Adapter\message_to_line_protocol(
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
