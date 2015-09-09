<?php
namespace InfluxDB\Type;

class BoolTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider boolProvider
     */
    public function testConversions($in, $out)
    {
        $this->assertEquals($out, new BoolType($in));
    }

    public function boolProvider()
    {
        return [
            [true, "true"],
            ["1", "true"],
            [1, "true"],
            ["something", "true"],
            [12, "true"],

            [false, "false"],
            ["0", "false"],
            [0, "false"],
            [null, "false"],
        ];
    }
}

