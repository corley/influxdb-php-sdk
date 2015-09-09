<?php
namespace InfluxDB\Type;

class FloatType
{
    private $num;

    public function __construct($value)
    {
        $this->num = floatval((string)$value);
    }

    public function __toString()
    {
        return (string)$this->num;
    }
}

