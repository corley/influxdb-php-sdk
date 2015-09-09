<?php
namespace InfluxDB\Type;

class BoolType
{
    private $num;

    public function __construct($value)
    {
        $this->num = boolval((string)$value);
    }

    public function __toString()
    {
        return ($this->num) ? "true" : "false";
    }
}

