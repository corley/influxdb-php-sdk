<?php
namespace InfluxDB\Integration\Adapter\Udp;

use InfluxDB\Adapter\Udp\Writer;
use InfluxDB\Adapter\Udp\Options as UdpOptions;
use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;

class WriterTest extends InfluxDBTestCase
{
    public function testWriteSimplePointsUsingDirectWrite()
    {
        $options = (new UdpOptions())->setPort(4444);
        $adapter = new Writer($options);

        $this->getClient()->createDatabase("udp.test");

        $adapter->write("cpu value=12.33 " . (int)(microtime(true)*1e9));

        sleep(2);

        $this->assertSerieExists("udp.test", "cpu");
        $this->assertSerieCount("udp.test", "cpu", 1);
        $this->assertValueExistsInSerie("udp.test", "cpu", "value", 12.33);
    }

    /**
     * @dataProvider getDifferentOptions
     */
    public function testWriteSimplePointsUsingSendMethod(UdpOptions $options)
    {
        $adapter = new Writer($options);

        $this->getClient()->createDatabase("udp.test");

        $adapter->send([
            "retentionPolicy" => "default",
            "points" => [
                [
                    "measurement" => "mem",
                    "fields" => [
                        "value" => 1233,
                        "value_float" => 1233.34,
                        "with_string" => "this is a string",
                        "with_bool" => true,
                    ],
                ],
            ],
        ]);

        sleep(2);

        $this->assertSerieExists("udp.test", "mem");
        $this->assertSerieCount("udp.test", "mem", 1);
        $this->assertValueExistsInSerie("udp.test", "mem", "value", 1233);
        $this->assertValueExistsInSerie("udp.test", "mem", "value_float", 1233.34);
        $this->assertValueExistsInSerie("udp.test", "mem", "with_string", "this is a string");
        $this->assertValueExistsInSerie("udp.test", "mem", "with_bool", true);
    }

    public function getDifferentOptions()
    {
        return [
            [(new UdpOptions())->setPort(4444)],
        ];
    }
}
