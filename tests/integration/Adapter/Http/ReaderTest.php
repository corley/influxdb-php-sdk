<?php
namespace InfluxDB\Integration\Adapter\Http;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use InfluxDB\Client;
use InfluxDB\Adapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;

class ReaderTest extends InfluxDBTestCase
{
    public function testReadSomeData()
    {
        $this->markTestIncomplete("Missing reader test cases");
    }
}

