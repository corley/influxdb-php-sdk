<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;
use GuzzleHttp\Client as GuzzleHttpClient;

class GuzzleAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $options;
    private $adapter;

    public function setUp()
    {
        $options = include __DIR__ . '/../bootstrap.php';
        $tcpOptions = $options["tcp"];

        $this->options = $options =  new Options();

        $options->setHost($tcpOptions["host"]);
        $options->setPort($tcpOptions["port"]);
        $options->setUsername($tcpOptions["username"]);
        $options->setPassword($tcpOptions["password"]);
        $options->setDatabase($tcpOptions["database"]);

        $guzzleHttp = new GuzzleHttpClient();
        $this->adapter = new GuzzleAdapter($guzzleHttp, $options);
    }

    public function testSuppressExceptions()
    {
        $this->options->setSuppressWriteExceptions(true);
        $this->options->setHost("localhost");
        $this->options->setPort(7356);
        $this->adapter->send(["ok" => "ciao"]);
    }

    /**
     * @expectedException GuzzleHttp\Exception\ConnectException
     */
    public function testNonSuppressExceptions()
    {
        $this->options->setSuppressWriteExceptions(false);
        $this->options->setHost("localhost");
        $this->options->setPort(7356);
        $this->adapter->send(["ok" => "ciao"]);
    }
}
