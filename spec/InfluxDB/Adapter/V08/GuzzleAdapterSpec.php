<?php

namespace spec\InfluxDB\Adapter\V08;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GuzzleHttp\Client;
use InfluxDB\Options;
use GuzzleHttp\Message\Response;

class GuzzleAdapterSpec extends ObjectBehavior
{
    function let(Client $client, Options $options)
    {
        $options->getHttpSeriesEndpoint()->willReturn("localhost");
        $options->getHttpDatabaseEndpoint()->willReturn("localhost");
        $options->getUsername()->willReturn("one");
        $options->getPassword()->willReturn("two");
        $this->beConstructedWith($client, $options);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Adapter\V08\GuzzleAdapter');
    }

    function it_should_send_data_via_post(Client $client, Options $options)
    {
        $client->post("localhost", [
            'auth' => ["one", "two"],
            'body' => json_encode(['pippo'])
        ])->shouldBeCalledTimes(1);

        $this->send(["pippo"]);
    }

    function it_should_query_data(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "query" => [
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(200,[],null));
        $this->query("select * from tcp.test")->shouldReturn(null);
    }

    function it_should_query_data_with_time_precision(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"],
                "query" => [
                    "time_precision" => "s",
                    "q" => "select * from tcp.test",
                ]
            ]
        )->willReturn(new Response(200, [], null));
        $this->query("select * from tcp.test", "s")->shouldReturn(null);
    }

    function it_should_list_all_databases(Client $client, Options $options)
    {
        $client->get(
            "localhost",
            [
                "auth" => ["one", "two"]
            ]
        )->shouldBeCalledTimes(1)->willReturn(new Response(200, [], null));

        $this->getDatabases()->shouldReturn(null);
    }

    function it_should_create_a_new_database(Client $client, Options $options)
    {
        $client->post(
            "localhost",
            [
                "auth" => ["one", "two"],
                "body" => json_encode(["name" => "db_name"])
            ]
        )->shouldBeCalledTimes(1)->willReturn(new Response(200, [], null));

        $this->createDatabase("db_name")->shouldReturn(null);
    }
}
