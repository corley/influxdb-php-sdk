<?php
namespace InfluxDB\Adapter;

use ReflectionMethod;
use InfluxDB\Options;

class AdapterAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToLineValues($message, $result, $options)
    {
        $helper = $this->getMockBuilder("InfluxDB\\Adapter\\AdapterAbstract")
            ->setConstructorArgs([$options])
            ->getMockForAbstractClass();

        $method = new ReflectionMethod(get_class($helper), "pointsToString");
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($helper, [$message]));
    }

    public function getElements()
    {
        return [
            [["one" => "two"], "one=\"two\"", new Options()],
            [["one" => "two", "three" => "four"], "one=\"two\",three=\"four\"", new Options()],
            [["one" => true, "three" => false], "one=true,three=false", new Options()],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0,four=1", new Options()],
            [["one" => true, "three" => false], "one=true,three=false", (new Options())->setForceIntegers(true)],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0i,four=1i", (new Options())->setForceIntegers(true)],
            [["one" => 12, "three" => 14], "one=12i,three=14i", (new Options())->setForceIntegers(true)],
            [["one" => 12.1, "three" => 14], "one=12.1,three=14i", (new Options())->setForceIntegers(true)],
            [["one" => 12., "three" => 14], "one=12,three=14i", (new Options())->setForceIntegers(true)],
            [["one" => (double)12, "three" => 14], "one=12,three=14i", (new Options())->setForceIntegers(true)],
            [["one" => (double)12, "three" => (double)14], "one=12,three=14", (new Options())->setForceIntegers(true)],
            [["one" => (double)"12", "three" => (int)"14"], "one=12,three=14i", (new Options())->setForceIntegers(true)],
        ];
    }
}
