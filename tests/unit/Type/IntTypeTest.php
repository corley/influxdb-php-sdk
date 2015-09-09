<?php
namespace InfluxDB\Type;

class IntTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider intProvider
     */
    public function testConversions($in, $out)
    {
        $this->assertEquals($out, new IntType($in));
    }

    public function intProvider()
    {
        return [
            ["12", "12i"],
            [12, "12i"],
            [12.12, "12i"],
            [new IntType(12.12), "12i"],
            [new IntType("12.12"), "12i"],
            ["invalid", "0i"],
            [null, "0i"],
            [new IntType("invalid"), "0i"],
            [new IntType(null), "0i"],
            [true, "1i"],
            [false, "0i"],
        ];
    }
}
