<?php
namespace InfluxDB;

use Prophecy\Argument;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testQueryCommandWithCallables()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("CREATE DATABASE mydb")->shouldBeCalledTimes(1);

        $manager = new Manager($client->reveal());
        $manager->addQuery("createDatabase", function($name) {
            return "CREATE DATABASE {$name}";
        });

        $manager->createDatabase("mydb");
    }

    public function testQueryCommandReturnsTheCommandData()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())->willReturn("OK");

        $manager = new Manager($client->reveal());
        $manager->addQuery("anything", function() {});

        $data = $manager->anything();
        $this->assertEquals("OK", $data);
    }

    public function testInvokableCommands()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("CREATE DATABASE mydb")->shouldBeCalledTimes(1);

        $manager = new Manager($client->reveal());
        $manager->addQuery(new CreateDatabaseMock());

        $manager->createDatabase("mydb");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotCallableMethods()
    {
        $client = $this->prophesize("InfluxDB\Client");

        $manager = new Manager($client->reveal());
        $manager->addQuery(new NotCallable());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testClassWithoutNameException()
    {
        $client = $this->prophesize("InfluxDB\Client");

        $manager = new Manager($client->reveal());
        $manager->addQuery(new NoName());
    }

    public function testFallbackToClientMethods()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->mark("hello", ["data" => true])->shouldBeCalledTimes(1);

        $manager = new Manager($client->reveal());
        $manager->mark("hello", ["data" => true]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The method you are using is not allowed: 'missingMethod', do you have to add it with 'addQuery'
     */
    public function testCallMissingMethod()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $manager = new Manager($client->reveal());
        $manager->missingMethod();
    }
}

class NoName
{
    public function __invoke($args)
    {
        return "TEST";
    }
}

class NotCallable
{
    public function __toString()
    {
        return "hello";
    }
}

class CreateDatabaseMock
{
    public function __invoke($name)
    {
        return "CREATE DATABASE {$name}";
    }

    public function __toString()
    {
        return "createDatabase";
    }
}
