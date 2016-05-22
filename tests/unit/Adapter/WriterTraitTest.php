<?php
namespace InfluxDB\Adapter;

use ReflectionMethod;
use InfluxDB\Type\IntType;
use InfluxDB\Type\FloatType;

class WriterTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToLineValues($message, $result)
    {
        $helper = $this->getMockBuilder("InfluxDB\\Adapter\\WriterTrait")
            ->getMockForTrait();

        $method = new ReflectionMethod(get_class($helper), "pointsToString");
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($helper, [$message]));
    }

    public function getElements()
    {
        return [
            [["one" => "two"], "one=\"two\""],
            [["one" => "two", "three" => "four"], "one=\"two\",three=\"four\""],
            [["one" => true, "three" => false], "one=true,three=false"],
            [["one" => true, "three" => 0., "four" => 1.], "one=true,three=0,four=1"],
            [["one" => true, "three" => false], "one=true,three=false"],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0i,four=1i"],
            [["one" => 12, "three" => 14], "one=12i,three=14i"],
            [["one" => 12.1, "three" => 14], "one=12.1,three=14i"],
            [["one" => 12., "three" => 14], "one=12,three=14i"],
            [["one" => (double)12, "three" => 14], "one=12,three=14i"],
            [["one" => (double)12, "three" => (double)14], "one=12,three=14"],
            [["one" => (double)"12", "three" => (int)"14"], "one=12,three=14i"],
            [["one" => (double)"12", "three" => new IntType("14")], "one=12,three=14i"],
            [["one" => (double)"12", "three" => new IntType(14.12)], "one=12,three=14i"],
            [["one" => (double)"12", "three" => new IntType(14)], "one=12,three=14i"],
            [["one" => (double)"12", "three" => new FloatType(14)], "one=12,three=14"],
            [["one" => (double)"12", "three" => new FloatType("14")], "one=12,three=14"],
            [["one" => (double)"12", "three" => new FloatType("14.123")], "one=12,three=14.123"],
        ];
    }

    /**
     * @dataProvider getMessages
     */
    public function testMessageToLineProtocolGeneratesValidTimeSeries($message, $prepared)
    {
        $helper = $this->getMockBuilder("InfluxDB\\Adapter\\WriterTrait")
            ->getMockForTrait();

        $actual = $helper->messageToLineProtocol($message);

        $this->assertEquals($prepared, $actual);
    }

    public function getMessages()
    {
        return [
            [
                [
                    "points" => [
                        [
                            "measurement" => "instance",
                            "fields" => [
                                "cpu" => 18.12,
                                "free" => 712423,
                            ],
                            "time" => "12345678m",
                        ]
                    ],
                ],
                "instance cpu=18.12,free=712423i 12345678m",
            ]
        ];
    }
}
