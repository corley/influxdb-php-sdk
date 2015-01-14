<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

class UdpAdapter implements AdapterInterface
{
    private $options;

    /**
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function send($message, $timePrecision = false)
    {
        $message = json_encode($message);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->options->getHost(), $this->options->getPort());
        socket_close($socket);
    }
}
