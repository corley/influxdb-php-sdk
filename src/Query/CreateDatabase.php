<?php
namespace InfluxDB\Query;

class CreateDatabase
{
    public function __invoke($name)
    {
        return "CREATE DATABASE \"" . addslashes($name) . "\"";
    }

    public function __toString()
    {
        return "createDatabase";
    }
}
