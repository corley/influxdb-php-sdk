<?php

namespace InfluxDB;

use InfluxDB\Adapter\Http\Reader as HttpReader;
use InfluxDB\Adapter\Http\Writer as HttpWriter;
use InfluxDB\Adapter\WritableInterface as Writer;
use InfluxDB\Adapter\QueryableInterface as Reader;

/**
 * Client to manage request at InfluxDB
 */
class Client
{
    private $reader;
    private $writer;

    // las constantes de precisión
    const PRECISION_NANOSECONDS = "ns";
    const PRECISION_MICROSECONDS_U = "µ";
    const PRECISION_MICROSECONDS = "u";
    const PRECISION_MILLISECONDS = "ms";
    const PRECISION_SECONDS = "s";
    const PRECISION_MINUTES = "m";
    const PRECISION_HOURS = "h";
    const PRECISION_RFC3339 = "rfc3339";

    public function __construct(Reader $reader, Writer $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    public function mark($name, array $values = [])
    {
        $data = $name;
        if (!is_array($name)) {
            $data =[];
            $data['points'][0]['measurement'] = $name;
            $data['points'][0]['fields'] = $values;
        }

        return $this->getWriter()->send($data);
    }

    public function query($query)
    {
        return $this->getReader()->query($query);
    }

    public static function validatePrecision($strPrecision) {
        return (in_array($strPrecision, [self::PRECISION_HOURS, self::PRECISION_MINUTES, self::PRECISION_SECONDS, self::PRECISION_MILLISECONDS, self::PRECISION_MICROSECONDS, self::PRECISION_MICROSECONDS_U, self::PRECISION_NANOSECONDS, self::PRECISION_RFC3339], TRUE) ? TRUE : FALSE);
    } // fin validatePrecision()

    public static function toValidQueryPrecision($strPrecision, $type=HttpReader::ENDPOINT) {
        switch ($type) {
            case HttpReader::ENDPOINT:
                switch ($strPrecision) {
                    case self::PRECISION_RFC3339: return NULL;
                    case self::PRECISION_MICROSECONDS_U: return self::PRECISION_MICROSECONDS;
                } // fin switch
                break;
            case HttpWriter::ENDPOINT:
                switch ($strPrecision) {
                    case self::PRECISION_RFC3339: return self::PRECISION_NANOSECONDS;
                    case self::PRECISION_MICROSECONDS_U: return self::PRECISION_MICROSECONDS;
                } // fin switch
                break;
        } // fin switch
        return $strPrecision;
    } // fin toValidQueryPrecision()
}