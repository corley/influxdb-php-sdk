<?php
namespace InfluxDB\Query;

class DeleteDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider queries
     */
    public function testDeleteDatabaseQuery($name, $query)
    {
        $db = new DeleteDatabase();
        $res = $db($name);

        $this->assertEquals($query, $res);
    }

    public function queries()
    {
        return [
            ["mydb", 'DROP DATABASE "mydb"'],
            ['my"db"', 'DROP DATABASE "my\"db\""'],
            ["my'db'", "DROP DATABASE \"my\'db\'\""],
        ];
    }
}

