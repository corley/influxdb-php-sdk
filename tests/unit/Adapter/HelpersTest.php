<?php
namespace InfluxDB\Adapter;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToLineValues($message, $result, $escape)
    {
        $this->assertEquals($result, list_to_string($message, $escape));
    }

    public function getElements()
    {
        return [
            [["one" => "two"], "one=two", false],
            [["one" => "two"], "one=\"two\"", true],
            [["one" => "two", "three" => "four"], "one=two,three=four", false],
            [["one" => "two", "three" => "four"], "one=\"two\",three=\"four\"", true],
            [["one" => true, "three" => false], "one=true,three=false", false],
            [["one" => true, "three" => 0, "four" => 1], "one=true,three=0,four=1", false],
        ];
    }
}
