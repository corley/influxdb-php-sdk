<?php
namespace InfluxDB\Adapter\Http;

use InfluxDB\Adapter\Http\Options;
use InfluxDB\Client as InfluxClient;
use GuzzleHttp\Client;
use InfluxDB\Adapter\QueryableInterface;

class Reader implements QueryableInterface
{
    private $httpClient;
    private $options;

    const ENDPOINT = "query";

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
        $objOpts = $this->getOptions();
        $options = [
            "auth" => [$objOpts->getUsername(), $objOpts->getPassword()],
            'query' => [
                "q" => $query,
                "db" => $objOpts->getDatabase(),
                "epoch"=>InfluxClient::toValidQueryPrecision($objOpts->getEpoch(), self::ENDPOINT)
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
        return $this->getHttpEndpoint(self::ENDPOINT);
    }

    private function getHttpEndpoint($operation)
    {
        $objOpts = $this->getOptions();
        $url = sprintf(
            "%s://%s:%d%s/%s",
            $objOpts->getProtocol(),
            $objOpts->getHost(),
            $objOpts->getPort(),
            $objOpts->getPrefix(),
            $operation
        );

        return $url;
    }
}
