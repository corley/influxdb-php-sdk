<?php
namespace InfluxDB\Filter;

interface FilterInterface
{
    /**
     * Filter metrics
     * @param mixed $anything
     */
    public function filter($anything);
}
