<?php
namespace InfluxDB\Query;

class DeleteDatabase
{
    public function __invoke($name)
    {
        return "DROP DATABASE \"" . addslashes($name) . "\"";
    }

    public function __toString()
    {
        return "deleteDatabase";
    }
}

