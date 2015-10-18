<?php
namespace InfluxDB;

use InvalidArgumentException;
use RuntimeException;
use InfluxDB\Client;

class Manager
{
    private $client;
    private $queries;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->queries = [];
    }

    public function addQuery($name, callable $query = null)
    {
        if ($query === null) {
            list($name, $query) = $this->fromObjectToNameCallableList($name);
        }

        $this->queries[$name] = $query;
    }

    private function fromObjectToNameCallableList($name)
    {
        if (is_object($name) && is_callable($name)) {
            if (method_exists($name, "__toString")) {
                return [(string)$name, $name];
            }
        }

        throw new InvalidArgumentException("Your command should implements '__toString' method and should be a callable thing");
    }

    public function __call($name, $args)
    {
        if (method_exists($this->client, $name)) {
            return call_user_func_array([$this->client, $name], $args);
        }

        if (array_key_exists($name, $this->queries)) {
            $query = call_user_func_array($this->queries[$name], $args);
            return $this->client->query($query);
        }

        throw new RuntimeException("The method you are using is not allowed: '{$name}', do you have to add it with 'addQuery'");
    }
}
