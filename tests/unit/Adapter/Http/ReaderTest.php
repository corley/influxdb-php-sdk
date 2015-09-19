<?php
namespace InfluxDB\Adapter\Http;

use DateTime;
use DateTimeZone;
use InfluxDB\Client;
use Prophecy\Argument;
use InfluxDB\Adapter\Http\Options;
use GuzzleHttp\Client as GuzzleHttpClient;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group tcp
     * @group proxy
     * @dataProvider getQueryEndpoints
     */
    public function testQueryEndpointGeneration($final, $options)
    {
        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new Reader($guzzleHttp, $options);

        $reflection = new \ReflectionClass(get_class($adapter));
        $method = $reflection->getMethod("getHttpQueryEndpoint");
        $method->setAccessible(true);

        $endpoint = $method->invokeArgs($adapter, []);
        $this->assertEquals($final, $endpoint);
    }

    public function getQueryEndpoints()
    {
        return [
            ["http://localhost:9000/query", (new Options())->setHost("localhost")->setPort(9000)],
            ["https://localhost:9000/query", (new Options())->setHost("localhost")->setPort(9000)->setProtocol("https")],
            ["http://localhost:9000/influxdb/query", (new Options())->setHost("localhost")->setPort(9000)->setPrefix("/influxdb")],
        ];
    }


}
