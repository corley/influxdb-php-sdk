<?php
namespace InfluxDB\Integration\Framework;

use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Client;
use InfluxDB\Manager;
use InfluxDB\Adapter\Http\Options as HttpOptions;
use InfluxDB\Adapter\Http\Writer;
use InfluxDB\Adapter\Http\Reader;
use InfluxDB\Query\CreateDatabase;
use InfluxDB\Query\DeleteDatabase;
use InfluxDB\Query\GetDatabases;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $options;

    public function setUp()
    {
        $options = $this->options = new HttpOptions();
        $guzzleHttp = new GuzzleHttpClient();
        $writer = new Writer($guzzleHttp, $options);
        $reader = new Reader($guzzleHttp, $options);

        $client = $this->client = new Manager(new Client($reader, $writer));

        $client->addQuery(new CreateDatabase());
        $client->addQuery(new DeleteDatabase());
        $client->addQuery(new GetDatabases());

        $this->dropAll();
    }

    public function tearDown()
    {
        $this->dropAll();
    }

    private function dropAll()
    {
        $databases = $this->getClient()->getDatabases();
        if (array_key_exists("values", $databases["results"][0]["series"][0])) {
            foreach ($databases["results"][0]["series"][0]["values"] as $database) {
                $this->getClient()->deleteDatabase($database[0]);
            }
        }
    }

    public function assertValueExistsInSerie($database, $serieName, $column, $value)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("select {$column} from \"{$serieName}\"");

        foreach ($body["results"][0]["series"][0]["values"] as $result) {
            if ($result[1] == $value) {
                return $this->assertTrue(true);
            }
        }

        return $this->fail("Missing value '{$value}'");
    }

    public function assertSerieCount($database, $serieName, $count)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("select * from \"{$serieName}\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
    }

    public function assertSerieExists($database, $serieName)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("show measurements");

        foreach ($body["results"][0]["series"][0]["values"] as $result) {
            if ($result[0] == $serieName) {
                return $this->assertTrue(true);
            }
        }

        return $this->fail("Missing serie with name '{$serieName}' in database '{$database}'");
    }

    public function assertDatabasesCount($count)
    {
        $databases = $this->client->getDatabases();
        $databaseList = [];
        if (array_key_exists("values", $databases["results"][0]["series"][0])) {
            $databaseList = $databases["results"][0]["series"][0]["values"];
        }

        $this->assertCount($count, $databaseList);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getClient()
    {
        return $this->client;
    }
}
