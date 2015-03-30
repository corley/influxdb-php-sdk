<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

/**
 * Clent adapter to call InfluxDb by UDP protocol
 * @link http://influxdb.com/docs/v0.6/api/reading_and_writing_data.html#writing-data-through-json-+-udp
 */
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

    public function createMessage($name, $values)
    {
        $data =[];

        $data['name'] = $name;
        $data['columns'] = array_keys($values);
        $data['points'][] = array_values($values);

        return $data;
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
