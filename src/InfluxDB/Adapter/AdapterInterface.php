<?php
namespace InfluxDB\Adapter;

interface AdapterInterface
{
    /**
     * Send series into database
     * @param mixed $message
     * @param string|boolean $timePrecision
     */
    public function send($message, $timePrecision = false);
}
