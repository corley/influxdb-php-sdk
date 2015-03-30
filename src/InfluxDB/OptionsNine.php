<?php

namespace InfluxDB;

/**
 * Manage in the best way InfluxDB Client Configuration
 */
class OptionsNine implements OptionsInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string|int
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $protocol;

    /**
     * Set default options
     */
    public function __construct()
    {
        $this->host = "localhost";
        $this->port = 8086;
        $this->username = "root";
        $this->password = "root";
        $this->setProtocol("http");
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     * @return Options
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
       return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string|int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string|int $port
     * @return Options
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $usarname
     * @return Options
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Options
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Build http series edpoint
     * @return string
     */
    public function getHttpSeriesEndpoint()
    {
        return sprintf(
            "%s://%s:%d",
            $this->getProtocol(),
            $this->getHost(),
            $this->getPort()
        );
    }

    /**
     * Build http database endpoint by name
     * @param string $name
     * @return string
     */
    public function getHttpDatabaseEndpoint()
    {
        $url = sprintf(
            "%s://%s:%d",
            $this->getProtocol(),
            $this->getHost(),
            $this->getPort()
        );

        return $url;
    }
}
