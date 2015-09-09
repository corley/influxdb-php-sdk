<?php
namespace InfluxDB\Type;

class FloatTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider floatProvider
     */
    public function testConversion($in, $out)
    {
        $in = new FloatType($in);
        $this->assertSame($out, (string)$in);
    }

    public function floatProvider()
    {
        return [
            [12.12, "12.12"],
            ["12.12", "12.12"],
            ["12", "12"],
            [12, "12"],
            [new FloatType("12.12"), "12.12"],
            [new FloatType(12.12), "12.12"],
            ["invalid", "0"],
            [null, "0"],
            [new FloatType("invalid"), "0"],
            [new FloatType(null), "0"],
            [true, "1"],
            [false, "0"],
        ];
    }
}
