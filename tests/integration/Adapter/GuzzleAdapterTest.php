<?php
namespace InfluxDB\Integration\Adapter;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use InfluxDB\Client;
use InfluxDB\Adapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;

class GuzzleAdapterTest extends InfluxDBTestCase
{
    /**
     * @dataProvider getDifferentOptions
     */
    public function testAdapterWriteDataCorrectly(Options $options)
    {
        $this->getClient()->createDatabase($options->getDatabase());

        $http = new GuzzleHttpClient();
        $adapter = new GuzzleAdapter($http, $options);

        $adapter->send([
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                        "valid" => true,
                        "overclock" => false,
                    ],
                ],
            ]
        ]);

        $this->assertSerieExists($options->getDatabase(), "vm-serie");
        $this->assertSerieCount($options->getDatabase(), "vm-serie", 1);
        $this->assertValueExistsInSerie($options->getDatabase(), "vm-serie", "cpu", 18.12);
        $this->assertValueExistsInSerie($options->getDatabase(), "vm-serie", "free", 712423);
        $this->assertValueExistsInSerie($options->getDatabase(), "vm-serie", "valid", true);
        $this->assertValueExistsInSerie($options->getDatabase(), "vm-serie", "overclock", false);
    }

    public function getDifferentOptions()
    {
        return [
            [(new Options())->setPort(8086)->setDatabase("tcp.test")],
            [(new Options())->setPort(8086)->setDatabase("tcp.test")->setForceIntegers(true)],
            [(new Options())->setPort(9000)->setDatabase("proxy.test")->setPrefix("/influxdb")],
        ];
    }
}
