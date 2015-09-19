<?php
namespace InfluxDB\Adapter;

use ReflectionMethod;
use InfluxDB\Type\IntType;
use InfluxDB\Type\FloatType;

class WriterAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToLineValues($message, $result)
    {
        $helper = $this->getMockBuilder("InfluxDB\\Adapter\\WriterAbstract")
            ->getMockForAbstractClass();

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
}
