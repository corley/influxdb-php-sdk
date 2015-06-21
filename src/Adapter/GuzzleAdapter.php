<?php
namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use InfluxDB\Options;
use InfluxDB\Helper\ErrorSuppression;

class GuzzleAdapter extends AdapterAbstract implements QueryableInterface
{
    use ErrorSuppression;

    private $httpClient;

    public function __construct(Client $httpClient, Options $options)
    {
        parent::__construct($options);

        $this->httpClient = $httpClient;
    }

    public function send(array $message)
    {
        $data = null;
        try {
            if ($this->getOptions()->getSuppressWriteExceptions()) {
                $this->suppressErrors();
            }

            $message = array_replace_recursive($this->getMessageDefaults(), $message);

            if (!count($message["tags"])) {
                unset($message["tags"]);
            }

            $httpMessage = [
                "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
                "body" => json_encode($message)
            ];

            $endpoint = $this->getHttpSeriesEndpoint();
            $data = $this->httpClient->post($endpoint, $httpMessage);
        } catch (\Exception $e) {
            if (!$this->getOptions()->getSuppressWriteExceptions()) {
                throw $e;
            }
        }

        return $data;
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
        return $this->httpClient->get($endpoint, $httpMessage)->json();
    }

    protected function getHttpSeriesEndpoint()
    {
        return sprintf(
            "%s://%s:%d%s/write",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort(),
            $this->getOptions()->getPrefix()
        );
    }

    protected function getHttpQueryEndpoint($name = false)
    {
        $url = sprintf(
            "%s://%s:%d%s/query",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort(),
            $this->getOptions()->getPrefix()
        );

        return $url;
    }
}
