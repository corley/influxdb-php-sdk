# InfluxDB PHP SDK

[![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
[![Code Coverage](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/corley/influxdb-php-sdk/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/corley/influxdb-sdk/v/stable)](https://packagist.org/packages/corley/influxdb-sdk)
[![License](https://poser.pugx.org/corley/influxdb-sdk/license)](https://packagist.org/packages/corley/influxdb-sdk)

Send metrics to InfluxDB and query for any data.

This project support InfluxDB API `>= 0.9` - **For InfluxDB v0.8 checkout branch 0.3**

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
    "corley/influxdb-sdk": ">=0.4"
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
$options = new Options();
$adapter = new UdpAdapter($options);

$client = new Client($adapter);
```

### Using HTTP Adapters

Actually Guzzle is used as HTTP client library

```php
<?php
$http = new \GuzzleHttp\Client();

$options = new Options();
$adapter = new GuzzleAdapter($http, $options);

$client = new Client($adapter);
```

## Create your client with the factory method

Effectively the client creation is not so simple, for that
reason you can you the factory method provided with the library.

```php
$options = [
    "adapter" => [
        "name" => "InfluxDB\\Adapter\\GuzzleAdapter",
        "options" => [
            // guzzle options
        ],
    ],
    "options" => [
        "host" => "my.influx.domain.tld",
        "db" => "mydb",
        "retention_policy" => "myPolicy",
        "tags" => [
            "env" => "prod",
            "app" => "myApp",
        ],
    ]
];
$client = \InfluxDB\ClientFactory::create($options);
```

Of course you can always use a DiC (eg `symfony/dependency-injection`) or your service manager in order to create
a valid client instance.

### Query InfluxDB

You can query the time series database using the query method.

```php
$influx->query('select * from "mine"');
```

You can query the database only if the adapter is queryable (implements
`QueryableInterface`), actually `GuzzleAdapter`.

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

## Database operations

You can create, list or destroy databases using dedicated methods

```php
$client->getDatabases(); // list all databases
$client->createDatabase("my-name"); // create a new database with name "my.name"
$client->deleteDatabase("my-name"); // delete an existing database with name "my.name"
```

Actually only queryable adapters can handle databases (implements the
`QueryableInterface`)

## Global tags and retention policy

You can set a set of default tags, that the SDK will add to your metrics:

```php
$options = new Options();
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

```
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

| PHP     | InfluxDB |
|---------|----------|
| int     | float64  |
| double  | float64  |
| boolean | boolean  |
| string  | string   |

```php
$client->mark("serie", [
    "value" => 12,  // Marked as float64
    "elem" => 12.4, // Marked as float64
]);
```

If you want to force integer values, we have to configure a particular option:

```php
$options->setForceIntegers(true); // disabled by default
```

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

**Pay attention! This option: `setForceIntegers` will be removed when InfluxDB
reaches a stable release `1.*` and we will enable the data type detection by
default (BC BREAK)**

### Force data type

If you want to ensure that a type is effectively parsed correctly you can force
it directly during the send operation

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

```
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

