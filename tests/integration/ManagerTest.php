<?php
namespace InfluxDB\Integration;

use InfluxDB\Adapter\Http\Options;
use InfluxDB\Adapter\Http\Writer;
use InfluxDB\Adapter\Http\Reader;
use InfluxDB\Client;
use InfluxDB\Query\CreateDatabase;
use InfluxDB\Query\DeleteDatabase;
use InfluxDB\Query\GetDatabases;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Manager;
use InfluxDB\Integration\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testCreateANewDatabase()
    {
        $options = new Options();
        $guzzleHttp = new GuzzleHttpClient();
        $writer = new Writer($guzzleHttp, $options);
        $reader = new Reader($guzzleHttp, $options);
        $client = new Client($reader, $writer);
        $manager = new Manager($client);

        $manager->addQuery(new CreateDatabase());
        $manager->addQuery(new DeleteDatabase());
        $manager->addQuery(new GetDatabases());

        $manager->createDatabase("one");
        $manager->createDatabase("two");
        $manager->createDatabase("walter");

        $databases = $manager->getDatabases();

        $this->assertCount(3, $databases["results"][0]["series"][0]["values"]);
    }

    public function testDropExistingDatabase()
    {
        $options = new Options();
        $guzzleHttp = new GuzzleHttpClient();
        $writer = new Writer($guzzleHttp, $options);
        $reader = new Reader($guzzleHttp, $options);
        $client = new Client($reader, $writer);
        $manager = new Manager($client);

        $manager->addQuery(new CreateDatabase());
        $manager->addQuery(new DeleteDatabase());
        $manager->addQuery(new GetDatabases());

        $manager->createDatabase("walter");
        $this->assertDatabasesCount(1);

        $manager->deleteDatabase("walter");
        $this->assertDatabasesCount(0);
    }

    public function testListActiveDatabases()
    {
        $options = new Options();
        $guzzleHttp = new GuzzleHttpClient();
        $writer = new Writer($guzzleHttp, $options);
        $reader = new Reader($guzzleHttp, $options);
        $client = new Client($reader, $writer);
        $manager = new Manager($client);

        $manager->addQuery(new CreateDatabase());
        $manager->addQuery(new DeleteDatabase());
        $manager->addQuery(new GetDatabases());

        $manager->createDatabase("one");
        $manager->createDatabase("two");

        $databases = $manager->getDatabases();

        $this->assertCount(2, $databases["results"][0]["series"][0]["values"]);
    }
}
