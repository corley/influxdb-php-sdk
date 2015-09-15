<?php
namespace InfluxDB\Adapter\Http;

use InfluxDB\Options;
use GuzzleHttp\Client;
use InfluxDB\Adapter\QueryableInterface;

class Reader implements QueryableInterface
{
    private $httpClient;
    private $options;

    public function __construct(Client $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function query($query)
    {
        $options = [
            "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
            'query' => [
                "q" => $query,
                "db" => $this->getOptions()->getDatabase(),
            ]
        ];

        return $this->get($options);
    }

    private function get(array $httpMessage)
    {
        $endpoint = $this->getHttpQueryEndpoint();
        return json_decode($this->httpClient->get($endpoint, $httpMessage)->getBody(), true);
    }

    protected function getHttpQueryEndpoint()
    {
        return $this->getHttpEndpoint("query");
    }

    private function getHttpEndpoint($operation)
    {
        $url = sprintf(
            "%s://%s:%d%s/%s",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort(),
            $this->getOptions()->getPrefix(),
            $operation
        );

        return $url;
    }
}
