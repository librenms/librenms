# InfluxDB PHP SDK

 * [![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
 * [![Dependency Status](https://www.versioneye.com/user/projects/54104e789e1622492d000025/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54104e789e1622492d000025)

Send metrics to InfluxDB and query for any data.

## Install it

Just use composer

```shell
php composer.phar require corley/influxdb-sdk:dev-master
```

Or place it in your require section

```json
{
  "require": {
    // ...
    "corley/influxdb-sdk": "dev-master"
  }
}
```

Add new points:

```php
$client->mark("app.search", [
    "key" => "this is my search"
]);
```

Retrieve existing points:

```php
$results = $client->query("select * from app.search");
```

## InfluxDB client adapters

Actually we supports two adapters

 * UDP/IP - in order to send data via UDP (datagram)
 * HTTP JSON - in order to send/retrieve using HTTP (connection oriented)

### Using UDP/IP Adapter

Actually "socket" php library is used for UDP/IP adapter

```
$options = new Options();
$adapter = new UdpAdapter($options);

$client = new Client();
$client->setAdapter($adapter);
```

### Using HTTP Adapter

Actually Guzzle is used as HTTP client library

```php
<?php
$guzzle = new \GuzzleHttp\Client();

$options = new Options();
$adapter = new GuzzleAdapter($guzzle, $options);

$client = new Client();
$client->setAdapter($adapter);
```

### Create your client with the factory method

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
    ],
    "filters" => [
        "query" => [
            "name" => "InfluxDB\\Filter\\ColumnsPointsFilter"
        ],
    ],
];
$client = \InfluxDB\ClientFactory::create($options);

$client->mark("error.404", ["page" => "/a-missing-page"]);
```

Of course you can always use the DiC or your service manager in
order to create a valid client instance.

### Time Precision write/read queries

You can set the `time_precision` for query query

```php
$client->mark("app.search", $points, "s"); //points contains "time" that is in seconds
```

```php
$client->query("select * from app.search", "s"); // retrieve points using seconds for time column
```

### Query InfluxDB

You can query the time series database using the query method.

```php
$influx->query("select * from mine");
$influx->query("select * from mine", "s"); // with time_precision
```

You can query the database only if the adapter is queryable (implements `QueryableInterface`),
actually `GuzzleAdapter`.

The adapter returns the json decoded body of the InfluxDB response, something like:

```
array(1) {
  [0] =>
  class stdClass#1 (3) {
    public $name =>
    string(8) "tcp.test"
    public $columns =>
    array(3) {
      [0] =>
      string(4) "time"
      [1] =>
      string(15) "sequence_number"
      [2] =>
      string(4) "mark"
    }
    public $points =>
    array(1) {
      [0] =>
      array(3) {
        [0] =>
        int(1410545635590)
        [1] =>
        int(2390001)
        [2] =>
        string(7) "element"
      }
    }
  }
}
```

By default data is returned as is. You can add filters in order to
change a response as you prefer, by default this library carries a
common filter that simplifies the response.

```
$client->setFilter(new ColumnsPointsFilter());

$data = $client->query("select * from hd_used");
```

With the "ColumnsPointsFilter" you get a list of dictionaries,
something like:

```
[
    "serie_name" => [
        [
            "time" => 410545635590,
            "sequence_number" => 390001,
            "mark" => "element",
        ],
    ]
]
```

## Database operations

You can create, list or destroy databases using dedicated methods

```php
$client->getDatabases(); // list all databases
$client->createDatabase("my.name"); // create a new database with name "my.name"
$client->deleteDatabase("my.name"); // delete an existing database with name "my.name"
```

Actually only queryable adapters can handle databases (implements the `QueryableInterface`)

