<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

class AdapterAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToLineValues($message, $result, $escape, $options)
    {
        $helper = $this->getMockBuilder("InfluxDB\\Adapter\\AdapterAbstract")
            ->setConstructorArgs([$options])
            ->getMockForAbstractClass();

        $this->assertEquals($result, $helper->listToString($message, $escape));
    }

    public function getElements()
    {
        return [
            [["one" => "two"], "one=two", false, new Options()],
            [["one" => "two"], "one=\"two\"", true, new Options()],
            [["one" => "two", "three" => "four"], "one=two,three=four", false, new Options()],
            [["one" => "two", "three" => "four"], "one=\"two\",three=\"four\"", true, new Options()],
            [["one" => true, "three" => false], "one=true,three=false", false, new Options()],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0,four=1", false, new Options()],
            [["one" => true, "three" => false], "one=true,three=false", false, (new Options())->setForceIntegers(true)],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0i,four=1i", false, (new Options())->setForceIntegers(true)],
            [["one" => 12, "three" => 14], "one=12i,three=14i", true, (new Options())->setForceIntegers(true)],
            [["one" => 12.1, "three" => 14], "one=12.1,three=14i", true, (new Options())->setForceIntegers(true)],
            [["one" => 12., "three" => 14], "one=12,three=14i", true, (new Options())->setForceIntegers(true)],
            [["one" => (double)12, "three" => 14], "one=12,three=14i", true, (new Options())->setForceIntegers(true)],
            [["one" => (double)12, "three" => (double)14], "one=12,three=14", true, (new Options())->setForceIntegers(true)],
            [["one" => (double)"12", "three" => (int)"14"], "one=12,three=14i", true, (new Options())->setForceIntegers(true)],
        ];
    }
}
