<?php
namespace InfluxDB\Type;

class StringType
{
    private $num;

    public function __construct($value)
    {
        $this->num = strval($value);
    }

    public function __toString()
    {
        return "\"" . $this->num . "\"";
    }
}

