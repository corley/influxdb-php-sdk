# InfluxDB PHP SDK

[![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
[![Code Coverage](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/corley/influxdb-sdk/v/stable)](https://packagist.org/packages/corley/influxdb-sdk)
[![License](https://poser.pugx.org/corley/influxdb-sdk/license)](https://packagist.org/packages/corley/influxdb-sdk)

Send metrics to InfluxDB and query for any data.

This project support InfluxDB API `>= 0.9` - **For InfluxDB v0.8 checkout branch 0.3 (no longer supported)**

Supported adapters:

 * UDP/IP
 * HTTP (via GuzzleHTTP versions: ~4, ~5, ~6)

## Install it

Just use composer

```sh
$ composer require corley/influxdb-sdk
```

Or add to your `composer.json` file

```json
{
  "require": {
    "corley/influxdb-sdk": "0.8.*"
  }
}
```

## Use it

Add new points:

```php
$client->mark("app-search", [
    "key" => "this is my search"
]);
```

Or use InfluxDB direct messages

```php
$client->mark([
    "tags" => [
        "dc" => "eu-west-1",
    ],
    "points" => [
        [
            "measurement" => "instance",
            "fields" => [
                "cpu" => 18.12,
                "free" => 712423,
            ],
        ],
    ]
]);
```

Retrieve existing points:

```php
$results = $client->query('select * from "app-search"');
```

## InfluxDB client adapters

Actually we supports two network adapters

 * UDP/IP - in order to send data via UDP/IP (datagram)
 * HTTP - in order to send/retrieve using HTTP messages (connection oriented)

### Using UDP/IP Adapter

In order to use the UDP/IP adapter your must have PHP compiled with the `sockets` extension.

**Usage**

```php
$reader = ...

$options = new Udp\Options();
$writer = new Udp\Writer($options);

$client = new Client($reader, $writer);
```

_UDP/IP option set have only `host` and `port` properties you configure
database and retention policies directly in your InfluxDB configuration_

### Using HTTP Adapters

Actually Guzzle is used as HTTP client library

```php
<?php
$http = new \GuzzleHttp\Client();

$writer = ...

$options = new Http\Options();
$reader = new Http\Reader($http, $options);

$client = new Client($reader, $writer);
```

## Mixing readers and writers

Of course you can mix Udp\Ip and Http adapters in order to write data points
with UDP/IP protocol but read information using HTTP.

```php
$reader = new Http\Reader($http, $httpOptions);
$writer = new Udp\Writer($udpOptions);
$client = new Client($reader, $writer);

$client->mark(...); // Use UDP/IP support
$client->query("SELECT * FROM my_serie"); // Use HTTP support
```

Or use only the HTTP

```php
$reader = new Http\Reader($http, $options);
$writer = new Http\Writer($http, $options);
$client = new Client($reader, $writer);

$client->mark(...); // Use HTTP support
$client->query("SELECT * FROM my_serie"); // Use HTTP support
```

### Query InfluxDB

You can query the time series database using the query method.

```php
$client->query('select * from "mine"');
```

The adapter returns the json decoded body of the InfluxDB response, something
like:

```
array(1) {
  'results' =>
  array(1) {
    [0] =>
    array(1) {
      'series' =>
      array(1) {
        ...
      }
    }
  }
}
```

If you prefere a more simple response than the original one, you can use
[`corley/influxdb-http-handlers`](https://github.com/corley/influxdb-http-handlers)
that convert, the original InfluxDB response, in a more simple response, something like:

```php
array(1) {
  'serie_name' => array(2) {
    [0] => array(4) {
      'time' => string(30) "2015-09-09T20:42:07.927267636Z"
      'value1' => int(1)
      'value2' => int(2)
      'valueS' => string(6) "string"
    }
    [1] => array(4) {
      'time' => string(30) "2015-09-09T20:42:51.332853369Z"
      'value1' => int(2)
      'value2' => int(4)
      'valueS' => string(11) "another-one"
    }
  }
}
```

## Global tags and retention policy

You can set a set of default tags, that the SDK will add to your metrics:

```php
$options = new Http\Options();
$options->setTags([
    "env" => "prod",
    "region" => "eu-west-1",
]);
```

The SDK mark all point adding those tags.

You can set a default retentionPolicy using

```
$options->setRetentionPolicy("myPolicy");
```

In that way the SDK use that policy instead of `default` policy.

## Proxies and InfluxDB

If you proxy your InfluxDB typically you have a prefix in your endpoints.

```php
$option->setHost("proxy.influxdb.tld");
$option->setPort(80);
$option->setPrefix("/influxdb"); // your prefix is: /influxdb

// final url will be: http://proxy.influxdb.tld:80/influxdb/write

$client->mark("serie", ["data" => "my-data"]);
```

## Data type management

From InfluxDB version `>=0.9.3` integer types are marked with a trailing `i`.
This library supports data types, in particular PHP types are mapped to influxdb
in this way by defaults:

And the resulting mapping will be:

| PHP     | InfluxDB |
|---------|----------|
| int     | int64    |
| double  | float64  |
| boolean | boolean  |
| string  | string   |

```php
$client->mark("serie", [
    "value" => 12,  // Marked as int64
    "elem" => 12.4, // Marked as float64
]);
```

### Force data type

If you want to ensure that a type is effectively parsed correctly you can force it directly during the send operation

```php
$client->mark("serie", [
    "value"  => new IntType(12),  // Marked as int64
    "elem"   => new FloatType(12.4), // Marked as float64
    "status" => new BoolType(true), // Marked as boolean
    "line"   => new StringType("12w"), // Marked as string
]);
```

### Query Builder

Interested in a Query Builder?

https://github.com/corley/dbal-influxdb

Thanks to Doctrine DBAL (Abstract Layer) you can use the query builder

```php
$qb = $conn->createQueryBuilder();

$qb->select("*")
    ->from("cpu_load_short")
    ->where("time = ?")
    ->setParameter(0, 1434055562000000000);

$data = $qb->execute();
foreach ($data->fetchAll() as $element) {
    // Use your element
}
```

```php
$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'dbname' => 'mydb',
    'user' => 'root',
    'password' => 'root',
    'host' => 'localhost',
    'port' => 8086,
    "driverClass" => "Corley\\DBAL\\Driver\\InfluxDB",
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
```

## Database operations and custom queries

The class `InfluxDB\Client` does not support any database operation by itself.
That means that you don't have any helper function for create new databases, or
list actual databases and so on.
Thanks to `InfluxDB\Manager` you can use a preset of queries and create your own
personal queries that will run against the Influxdb instance.

### Create the manager

The `Manager` instance is very simple, you have to create it with a client
instance.

```php
$manager = new Manager($client); // InfluxDB\Client instance
```

### Attach new queries

The manager allows to attach new queries via an helper method `addQuery`.

```php
$manager->addQuery("getExceptionsInMinutes", function($minutes) {
    return "SELECT * FROM app_exceptions WHERE time > now() - {$minutes}m";
});

$manager->getExceptionsInMinutes(10); // The callable name
```

As you can see you have to label your anonymous function and reuse it via the
manager.

In order to collect and reuse custom queries you can define query objects:

```php
class GetExceptionsInMinutes
{
    public function __invoke($minutes)
    {
        return "SELECT * FROM app_exceptions WHERE time > now() - {$minutes}m";
    }

    public function __toString()
    {
        return "getExceptionsInMinutes";
    }
}

$manager->addQuery(new GetExceptionsInMinutes());

$manger->getExceptionsInMinutes(10); //Use the query
```

As you can see valid query command should be `callable` via the `__invoke`
method and should be also serializable as strings via `__toString` method

### Existing queries

This project comes out with a preset of valid queries:

 * Create new database `InfluxDB\Query\CreateDatabase`
 * Drop existing databases `InfluxDB\Query\DeleteDatabase`
 * List existing databases `InfluxDB\Query\GetDatabases`

```php
$manager->addQuery(new CreateDatabase());
$manager->addQuery(new DeleteDatabase());
$manager->addQuery(new GetDatabases());
```

## FAQ

### Add sockets support to your PHP

To verify if you have the `sockets` extension just issue a:

```bash
php -m | grep sockets
```

If you don't have the `sockets` extension, you can proceed in two ways:

  - Recompile your PHP whith the `--enable-sockets` flag
  - Or just compile the `sockets` extension extracting it from the PHP source.

  1. Download the source relative to the PHP version that you on from [here](https://github.com/php/php-src/releases)
  2. Enter in the `ext/sockets` directory
  3. Issue a `phpize && ./configure && make -j && sudo make install`
  4. Add `extension=sockets.so` to your php.ini

