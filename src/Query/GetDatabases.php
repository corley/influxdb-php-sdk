<?php
namespace InfluxDB\Query;

class GetDatabases
{
    public function __invoke()
    {
        return "show databases";
    }

    public function __toString()
    {
        return "getDatabases";
    }
}


