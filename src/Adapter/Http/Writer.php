<?php
namespace InfluxDB\Adapter\Http;

use GuzzleHttp\Client;
use InfluxDB\Adapter\WriterTrait;
use InfluxDB\Adapter\Http\Options;
use InfluxDB\Adapter\WritableInterface;
use InfluxDB\Client as InfluxClient;

class Writer implements WritableInterface
{
    use WriterTrait;

    private $httpClient;
    private $options;

    const ENDPOINT = "write";

    public function __construct(Client $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function send(array $message)
    {
        $objOptions = $this->getOptions();
        $httpMessage = [
            "auth" => [$objOptions->getUsername(), $objOptions->getPassword()],
            'query' => [
                "db" => $objOptions->getDatabase(),
                "retentionPolicy" => $objOptions->getRetentionPolicy(),
                "precision"=>InfluxClient::toValidQueryPrecision($objOpts->getPrecision(), self::ENDPOINT)
            ],
            "body" => $this->messageToLineProtocol($message, $objOptions->getTags())
        ];

        $endpoint = $this->getHttpSeriesEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage);
    }

    protected function getHttpSeriesEndpoint()
    {
        return $this->getHttpEndpoint(self::ENDPOINT);
    }

    private function getHttpEndpoint($operation)
    {
        $objOptions = $this->getOptions();
        $url = sprintf(
            "%s://%s:%d%s/%s",
            $objOptions->getProtocol(),
            $objOptions->getHost(),
            $objOptions->getPort(),
            $objOptions->getPrefix(),
            $operation
        );

        return $url;
    }
}
