# InfluxDB PHP SDK

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/699a9a78-39aa-41d0-bb60-41dbf0f1251d/big.png)](https://insight.sensiolabs.com/projects/699a9a78-39aa-41d0-bb60-41dbf0f1251d)

 * Master: [![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
 * Develop: [![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=develop)](https://travis-ci.org/corley/influxdb-php-sdk)

Send metrics to InfluxDB and query for any data.

**For InfluxDB v0.8 checkout branch 0.3**

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

Or use InfluxDB direct messages

```php
$client->mark([
    "database" => "mydb",
    "tags" => [
        "dc" => "eu-west-1",
    ],
    "points" => [
        [
            "name" => "vm-serie",
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
$results = $client->query("select * from app.search");
```

## InfluxDB client adapters

Actually we supports two adapters

 * UDP/IP - in order to send data via UDP (datagram)
 * HTTP JSON - in order to send/retrieve using HTTP (connection oriented)

### Using UDP/IP Adapter


In order to use the UDP/IP adapter your must have PHP compiled with the `sockets` extension.

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

**Usage**

```php
$options = new Options();
$adapter = new UdpAdapter($options);

$client = new Client();
$client->setAdapter($adapter);
```

### Using HTTP Adapters

Actually Guzzle is used as HTTP client library

```php
<?php
$http = new \GuzzleHttp\Client();

$options = new Options();
$adapter = new HttpAdapter($http, $options);

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
        "db" => "mydb",
    ]
];
$client = \InfluxDB\ClientFactory::create($options);

$client->mark("error.404", ["page" => "/a-missing-page"]);
```

Of course you can always use the DiC or your service manager in order to create
a valid client instance.

### Query InfluxDB

You can query the time series database using the query method.

```php
$influx->query('select * from "mine"');
```

You can query the database only if the adapter is queryable (implements
`QueryableInterface`), actually `HttpAdapter`.

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
$client->createDatabase("my.name"); // create a new database with name "my.name"
$client->deleteDatabase("my.name"); // delete an existing database with name "my.name"
```

Actually only queryable adapters can handle databases (implements the
`QueryableInterface`)

## Benchmarks

### Adapters

The impact using UDP or HTTP adapters

```
Corley\Benchmarks\InfluxDB\AdapterEvent
    Method Name                Iterations    Average Time      Ops/second
    ------------------------  ------------  --------------    -------------
    sendDataUsingHttpAdapter: [1,000     ] [0.0098177127838] [101.85672]
    sendDataUsingUdpAdapter : [1,000     ] [0.0000694372654] [14,401.48880]
```

