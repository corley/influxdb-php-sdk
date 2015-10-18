<?php

namespace InfluxDB;

use InfluxDB\Adapter\WritableInterface as Writer;
use InfluxDb\Adapter\QueryableInterface as Reader;

/**
 * Client to manage request at InfluxDB
 */
class Client
{
    private $reader;
    private $writer;

    public function __construct(Reader $reader, Writer $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    public function mark($name, array $values = [])
    {
        $data = $name;
        if (!is_array($name)) {
            $data =[];
            $data['points'][0]['measurement'] = $name;
            $data['points'][0]['fields'] = $values;
        }

        return $this->getWriter()->send($data);
    }

    public function query($query)
    {
        return $this->getReader()->query($query);
    }
}
