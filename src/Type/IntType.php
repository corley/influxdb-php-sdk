<?php
namespace InfluxDB\Type;

class IntType
{
    private $num;

    public function __construct($value)
    {
        $this->num = intval((string)$value);
    }

    public function __toString()
    {
        return $this->num . "i";
    }
}
