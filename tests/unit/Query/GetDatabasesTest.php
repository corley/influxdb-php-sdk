<?php
namespace InfluxDB\Query;

class GetDatabasesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDatabasesQuery()
    {
        $db = new GetDatabases();
        $res = $db();

        $this->assertEquals("show databases", $res);
    }
}


