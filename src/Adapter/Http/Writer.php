<?php
namespace InfluxDB\Adapter\Http;

use InfluxDB\Options;
use GuzzleHttp\Client;
use InfluxDB\Adapter\WriterAbstract;

class Writer extends WriterAbstract
{
    private $httpClient;

    public function __construct(Client $httpClient, Options $options)
    {
        parent::__construct($options);

        $this->httpClient = $httpClient;
    }

    public function send(array $message)
    {
        $httpMessage = [
            "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
            'query' => [
                "db" => $this->getOptions()->getDatabase(),
                "retentionPolicy" => $this->getOptions()->getRetentionPolicy(),
            ],
            "body" => $this->messageToLineProtocol($message)
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
