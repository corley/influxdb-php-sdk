<?php
namespace InfluxDB\Integration\Adapter\Udp;

use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;
use InfluxDB\Options;
use InfluxDB\Adapter\Udp\Writer;

class WriterTest extends InfluxDBTestCase
{
    public function testWriteSimplePointsUsingDirectWrite()
    {
        $options = (new Options())->setPort(4444);
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
    public function testWriteSimplePointsUsingSendMethod(Options $options)
    {
        $adapter = new Writer($options);

        $this->getClient()->createDatabase($options->getDatabase());

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

        $this->assertSerieExists($options->getDatabase(), "mem");
        $this->assertSerieCount($options->getDatabase(), "mem", 1);
        $this->assertValueExistsInSerie($options->getDatabase(), "mem", "value", 1233);
        $this->assertValueExistsInSerie($options->getDatabase(), "mem", "value_float", 1233.34);
        $this->assertValueExistsInSerie($options->getDatabase(), "mem", "with_string", "this is a string");
        $this->assertValueExistsInSerie($options->getDatabase(), "mem", "with_bool", true);
    }

    public function getDifferentOptions()
    {
        return [
            [(new Options())->setPort(4444)->setDatabase("udp.test")],
        ];
    }
}
