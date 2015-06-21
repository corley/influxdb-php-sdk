<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

class UdpAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $options;
    private $object;

    public function setUp()
    {
        $this->options = new Options();
        $this->object = new UdpAdapter($this->options);
    }

    public function tearDown()
    {
        restore_exception_handler();
        restore_error_handler();
    }

    public function testSuppressErrors()
    {
        $this->options->setHost("invalid-host");
        $this->options->setSuppressWriteExceptions(true);

        $restored = false;
        set_error_handler(function() use (&$restored) {
            $restored = true;
        });

        $this->object->write("something");

        $this->assertFalse($restored);
    }

    public function testRestoreDefaultErrorHandler()
    {
        $this->options->setSuppressWriteExceptions(true);

        $restored = false;
        set_error_handler(function() use (&$restored) {
            $restored = true;
        });

        $this->object->write("something");

        trigger_error("hi", E_USER_NOTICE);
        $this->assertTrue($restored);
    }

    public function testDisableErrorSuppression()
    {
        $this->options->setHost("invalid-host");
        $this->options->setSuppressWriteExceptions(false);

        $restored = false;
        set_error_handler(function() use (&$restored) {
            $restored = true;
        });

        $this->object->write("something");

        $this->assertTrue($restored);
    }


    /**
     * @dataProvider getMessages
     */
    public function testRewriteMessages($input, $response)
    {
        $object = new UdpAdapter(new Options());
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod("serialize");
        $method->setAccessible(true);

        $message = $method->invokeArgs($object, [$input]);

        $this->assertEquals($response, $message);
    }

    public function getMessages()
    {
        return [
            [
                [
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "value" => 1,
                            ],
                        ],
                    ],
                ],
                "cpu value=1 1257894000000000000"
            ],
            [
                [
                    "tags" => [
                        "region" => "us-west",
                        "host" => "serverA",
                        "env" => "prod",
                        "target" => "servers",
                        "zone" => "1c",
                    ],
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "cpu" => 18.12,
                                "free" => 712432,
                            ],
                        ],
                    ],
                ],
                "cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12,free=712432 1257894000000000000"
            ],
            [
                [
                    "tags" => [
                        "region" => "us-west",
                        "host" => "serverA",
                        "env" => "prod",
                        "target" => "servers",
                        "zone" => "1c",
                    ],
                    "time" => "2009-11-10T23:00:00Z",
                    "points" => [
                        [
                            "measurement" => "cpu",
                            "fields" => [
                                "cpu" => 18.12,
                            ],
                        ],
                        [
                            "measurement" => "mem",
                            "fields" => [
                                "free" => 712432,
                            ],
                        ],
                    ],
                ],
                <<<EOF
cpu,region=us-west,host=serverA,env=prod,target=servers,zone=1c cpu=18.12 1257894000000000000
mem,region=us-west,host=serverA,env=prod,target=servers,zone=1c free=712432 1257894000000000000
EOF
            ],
        ];
    }
}
