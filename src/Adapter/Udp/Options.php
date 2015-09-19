<?php
namespace InfluxDB\Adapter\Udp;

class Options
{
    private $host;
    private $port;
    private $tags;

    public function __construct()
    {
        $this->setHost("localhost");
        $this->setTags([]);
        $this->setPort(4444);
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    public function getHost()
    {
       return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
}

