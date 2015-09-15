<?php
namespace InfluxDB;

use DateTime;
use DateTimeZone;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Options;
use InfluxDB\Adapter\UdpAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testMarkNewMeasurementWithShortSyntax()
    {
        $reader = $this->prophesize("InfluxDB\\Adapter\\QueryableInterface");
        $writer = $this->prophesize("InfluxDB\\Adapter\\WritableInterface");
        $writer->send([
            "points" => [
                [
                    "measurement" => "tcp.test",
                    "fields" => [
                        "mark" => "element"
                    ]
                ]
            ]
        ])->shouldBeCalledTimes(1);

        $object = new Client($reader->reveal(), $writer->reveal());
        $object->mark("tcp.test", ["mark" => "element"]);
    }

    public function testWriteDirectMessages()
    {
        $reader = $this->prophesize("InfluxDB\\Adapter\\QueryableInterface");
        $writer = $this->prophesize("InfluxDB\\Adapter\\WritableInterface");
        $writer->send([
            "tags" => [
                "dc" => "eu-west-1",
            ],
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                    ]
                ]
            ]
        ])->shouldBeCalledTimes(1);
        $object = new Client($reader->reveal(), $writer->reveal());

        $object->mark([
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
        ]);
    }
}
