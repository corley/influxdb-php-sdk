<?php

namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Message\ResponseInterface;
use InfluxDB\Exception\InfluxAuthorizationException;
use InfluxDB\Exception\InfluxBadResponseException;
use InfluxDB\Exception\InfluxGeneralException;
use InfluxDB\Exception\InfluxNoSeriesException;
use InfluxDB\Options;

class HttpAdapter implements AdapterInterface, QueryableInterface
{
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_UNAUTHORIZED = 401;
    const STATUS_CODE_FORBIDDEN = 403;
    const STATUS_CODE_BAD_REQUEST = 400;

    /**
     * @var \InfluxDB\Options
     */
    private $options;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Options $options
     */
    public function __construct(Options $options, Client $client = null)
    {
        $this->options = $options;
        $this->client = $client ?: new Client();
    }

    /**
     * @param array $body
     * @param array $query
     * @param bool $timePrecision
     * @return array
     */
    protected function getRequest(array $body = [], array $query = [], $timePrecision = false)
    {
        $request = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "exceptions" => false
        ];
        if (count($body)) {
            $request['body'] = json_encode($body);
        }
        if (count($query)) {
            $request['query'] = $query;
        }
        if ($timePrecision) {
            $request["query"]["time_precision"] = $timePrecision;
        }
        return $request;
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws \InfluxDB\Exception\InfluxGeneralException
     * @throws \InfluxDB\Exception\InfluxAuthorizationException
     * @throws \InfluxDB\Exception\InfluxNoSeriesException
     */
    protected function parseResponse(ResponseInterface $response)
    {
        switch ($response->getStatusCode()) {
            case self::STATUS_CODE_OK :
                try {
                    return $response->json();
                } catch (ParseException $ex) {
                    throw new InfluxBadResponseException(
                        sprintf("%s; Response is '%s'", $ex->getMessage(), (string)$response->getBody()),
                        $ex->getCode(), $ex
                    );
                }
            case self::STATUS_CODE_UNAUTHORIZED:
            case self::STATUS_CODE_FORBIDDEN:
                throw new InfluxAuthorizationException((string)$response->getBody(), $response->getStatusCode());
            case self::STATUS_CODE_BAD_REQUEST:
                if (strpos((string)$response->getBody(), "Couldn't find series:") !== false) {
                    throw new InfluxNoSeriesException((string)$response->getBody(), $response->getStatusCode());
                }
                throw new InfluxGeneralException((string)$response->getBody(), $response->getStatusCode());
            default:
                throw new InfluxGeneralException((string)$response->getBody(), $response->getStatusCode());
        }
    }

    /**
     * @param $message
     * @param bool $timePrecision
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function send($message, $timePrecision = false)
    {
        try {
            $response = $this->client->post(
                $this->options->getHttpSeriesEndpoint(),
                $this->getRequest($message, [], $timePrecision)
            );
        } catch (\Exception $ex) {
            throw new InfluxGeneralException($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this->parseResponse($response);
    }

    /**
     * @param $query
     * @param bool $timePrecision
     * @return mixed
     */
    public function query($query, $timePrecision = false)
    {
        try {
            $response = $this->client->get(
                $this->options->getHttpSeriesEndpoint(),
                $this->getRequest([], ["q" => $query], $timePrecision)
            );
        } catch (\Exception $ex) {
            throw new InfluxGeneralException($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this->parseResponse($response);
    }

    /**
     * @return mixed
     */
    public function getDatabases()
    {
        try {
            $response = $this->client->get(
                $this->options->getHttpDatabaseEndpoint(),
                $this->getRequest()
            );
        } catch (\Exception $ex) {
            throw new InfluxGeneralException($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this->parseResponse($response);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function createDatabase($name)
    {
        try {
            $response = $this->client->post(
                $this->options->getHttpDatabaseEndpoint(),
                $this->getRequest(["name" => $name])
            );
        } catch (\Exception $ex) {
            throw new InfluxGeneralException($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this->parseResponse($response);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function deleteDatabase($name)
    {
        try {
            $response = $this->client->delete(
                $this->options->getHttpDatabaseEndpoint($name),
                $this->getRequest()
            );
        } catch (\Exception $ex) {
            throw new InfluxGeneralException($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this->parseResponse($response);
    }
}