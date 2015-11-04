<?php

namespace InfluxDB;

use InfluxDB\Adapter\WritableInterface as Writer;
use InfluxDB\Adapter\QueryableInterface as Reader;

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

    public function getDatabases()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 0.8.1 and will be removed in 0.9.', E_USER_DEPRECATED);
        return $this->query("show databases");
    }

    public function createDatabase($name)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 0.8.1 and will be removed in 0.9.', E_USER_DEPRECATED);
        return $this->query("create database \"{$name}\"");
    }

    public function deleteDatabase($name)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 0.8.1 and will be removed in 0.9.', E_USER_DEPRECATED);
        return $this->query("drop database \"{$name}\"");
    }
}
