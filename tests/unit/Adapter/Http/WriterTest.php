<?php
namespace InfluxDB\Adapter\Http;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Client;
use Prophecy\Argument;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group tcp
     * @group proxy
     * @dataProvider getWriteEndpoints
     */
    public function testWriteEndpointGeneration($final, $options)
    {
        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new Writer($guzzleHttp, $options);

        $reflection = new \ReflectionClass(get_class($adapter));
        $method = $reflection->getMethod("getHttpSeriesEndpoint");
        $method->setAccessible(true);

        $endpoint = $method->invokeArgs($adapter, []);
        $this->assertEquals($final, $endpoint);
    }

    public function getWriteEndpoints()
    {
        return [
            ["http://localhost:9000/write", (new Options())->setHost("localhost")->setPort(9000)],
            ["https://localhost:9000/write", (new Options())->setHost("localhost")->setPort(9000)->setProtocol("https")],
            ["http://localhost:9000/influxdb/write", (new Options())->setHost("localhost")->setPort(9000)->setPrefix("/influxdb")],
        ];
    }

    public function testMergeWithDefaultOptions()
    {
        $options = new Options();
        $options->setDatabase("db");
        $httpClient = $this->prophesize("GuzzleHttp\\Client");
        $httpClient->post(Argument::Any(), [
            "auth" => ["root", "root"],
            "query" => [
                "db" => "db",
                "retentionPolicy" => "default",
            ],
            "body" => null,
        ])->shouldBeCalledTimes(1);
        $adapter = new Writer($httpClient->reveal(), $options);
        $adapter->send([]);
    }

    /**
     * @dataProvider getMessages
     */
    public function testMessageComposition($options, $send, $regexp)
    {
        $guzzleHttp = $this->prophesize("GuzzleHttp\Client");
        $guzzleHttp->post("http://localhost:8086/write", Argument::that(function($val) use ($regexp) {
            $body = $val["body"];
            $this->assertRegExp($regexp, $body);
            return true;
        }))->shouldBeCalledTimes(1);
        $adapter = new Writer($guzzleHttp->reveal(), $options);

        $adapter->send($send);
    }

    public function getMessages()
    {
        return [
            [
                (new Options())->setDatabase("db"),
                [
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test mark="element" 1257894000000000000/i'
            ],
            [
                (new Options())->setDatabase("db"),
                [
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ],
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element2"
                            ]
                        ],
                    ]
                ],
                '/tcp.test mark="element" \d+\ntcp.test mark="element2" \d+/i'
            ],
            [
                (new Options())->setDatabase("db"),
                [
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test mark="element" \d+/i'
            ],
            [
                (new Options())->setDatabase("db"),
                [
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "time" => "2009-11-10T23:00:00Z",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test mark="element" 1257894000000000000/i'
            ],
            [
                (new Options())->setDatabase("db"),
                [
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "time" => "2009-11-10T23:00:00Z",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ],
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element2"
                            ]
                        ],
                    ]
                ],
                '/tcp.test mark="element" 1257894000000000000\ntcp.test mark="element2" 1257894000000000000$/i'
            ],
            [
                (new Options())->setDatabase("db"),
                [
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "time" => "2009-11-10T23:00:00Z",
                            "fields" => [
                                "mark" => "element",
                            ]
                        ]
                    ]
                ],
                '/tcp.test mark="element" 1257894000000000000$/i'
            ],
            [
                (new Options())->setDatabase("db")->setTags(["dc" => "us-west"]),
                [
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test,dc=us-west mark="element" \d+/i'
            ],
            [
                (new Options())->setDatabase("db")->setTags(["dc" => "us-west"]),
                [
                    "tags" => ["region" => "us"],
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test,dc=us-west,region=us mark="element" \d+/i'
            ],
            [
                (new Options())->setDatabase("db")->setTags(["dc" => "us-west"]),
                [
                    "tags" => ["region" => "us"],
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "tags" => [
                                "tt" => "fi",
                            ],
                            "fields" => [
                                "mark" => "element"
                            ]
                        ]
                    ]
                ],
                '/tcp.test,dc=us-west,region=us,tt=fi mark="element" \d+$/i'
            ],
            [
                (new Options())->setDatabase("db")->setTags(["dc" => "us-west"]),
                [
                    "tags" => ["region" => "us"],
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element"
                            ]
                        ],
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element2"
                            ]
                        ]
                    ]
                ],
                '/tcp.test,dc=us-west,region=us mark="element" \d+\ntcp.test,dc=us-west,region=us mark="element2" \d+$/i'
            ],
            [
                (new Options())->setDatabase("db")->setForceIntegers(true),
                [
                    "points" => [
                        [
                            "measurement" => "tcp.test",
                            "fields" => [
                                "mark" => "element",
                                "value" => 12,
                            ]
                        ]
                    ]
                ],
                '/tcp.test mark="element",value=12i \d+/i'
            ],
        ];
    }
}
