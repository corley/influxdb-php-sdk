<?php
namespace InfluxDB;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group factory
     *
     * @expectedException InvalidArgumentException
     */
    public function testEmptyOptions()
    {
        $client = ClientFactory::create([]);
    }

    /**
     * @group factory
     */
    public function testDefaultParams()
    {
        $client = ClientFactory::create(["adapter" => ["name" => "InfluxDB\\Adapter\\V08\\GuzzleAdapter"]]);

        $this->assertNull($client->getFilter());
    }

    /**
     * @group factory
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAdapter()
    {
        $client = ClientFactory::create(["adapter" => ["name" => "UdpAdapter"]]);
    }

    /**
     * @group factory
     * @group udp
     */
    public function testCreateUdpClient()
    {
        $options = [
            "adapter" => [
                "name" => "InfluxDB\\Adapter\\V08\\UdpAdapter",
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf("InfluxDB\\Adapter\\V08\\UdpAdapter", $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
    }

    /**
     * @group factory
     * @group tcp
     * @dataProvider getTcpAdapters
     */
    public function testCreateTcpClient($adapter)
    {
        $options = [
            "adapter" => [
                "name" => $adapter,
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf($adapter, $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());
    }

    public function getTcpAdapters()
    {
        return [
            ["InfluxDB\\Adapter\\V08\\GuzzleAdapter"],
            ["InfluxDB\\Adapter\\V08\\HttpAdapter"],
        ];
    }

    /**
     * @group factory
     * @group filters
     * @dataProvider getTcpAdapters
     */
    public function testCreateTcpClientWithFilter($adapter)
    {
        $options = [
            "adapter" => [
                "name" => $adapter,
            ],
            "options" => [
                "host" => "127.0.0.1",
                "username" => "user",
                "password" => "pass",
            ],
            "filters" => [
                "query" => [
                    "name" => "InfluxDB\\Filter\\ColumnsPointsFilter",
                ],
            ],
        ];

        $client = ClientFactory::create($options);
        $this->assertInstanceOf("InfluxDB\\Client", $client);

        $this->assertInstanceOf($adapter, $client->getAdapter());
        $this->assertEquals("127.0.0.1", $client->getAdapter()->getOptions()->getHost());
        $this->assertEquals("user", $client->getAdapter()->getOptions()->getUsername());
        $this->assertEquals("pass", $client->getAdapter()->getOptions()->getPassword());

        $this->assertInstanceOf("InfluxDB\\Filter\\ColumnsPointsFilter", $client->getFilter());
    }
}
