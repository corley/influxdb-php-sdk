<?php
namespace InfluxDB\Adapter\Http;

use GuzzleHttp\Client;
use InfluxDB\Adapter\Http\Options;
use InfluxDB\Adapter\WriterAbstract;

class Writer extends WriterAbstract
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

    public function send(array $message)
    {
        $httpMessage = [
            "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
            'query' => [
                "db" => $this->getOptions()->getDatabase(),
                "retentionPolicy" => $this->getOptions()->getRetentionPolicy(),
            ],
            "body" => $this->messageToLineProtocol($message, $this->getOptions()->getTags())
        ];

        $endpoint = $this->getHttpSeriesEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage);
    }

    protected function getHttpSeriesEndpoint()
    {
        return $this->getHttpEndpoint("write");
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
