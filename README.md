# InfluxDB PHP SDK

 * [![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
 * [![Dependency Status](https://www.versioneye.com/user/projects/54104e789e1622492d000025/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54104e789e1622492d000025)

Send metrics to InfluxDB.

```php
$options = new \InfluxDB\Options();

$client = new \InfluxDB\Client();
$client->setAdapter(new \InfluxDB\Adapter\UdpAdapter($options));

$client->mark("search", [
    "query" => "php"
]);
```

## Install it

Just use composer

```shell
php composer.phar require corley/influxdb-sdk:*
```

Or place it in your require section

```json
{
  "require": {
    // ...
    "corley/influxdb-sdk": "*"
  }
}
```

## Send data using HTTP json API

Actually we using Guzzle as HTTP client

```php
$influx->mark("tcp.test", ["mark" => "element"]);
```

## Query InfluxDB

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

## Prepare lib dependencies

Use your DiC or Service Locator in order to provide a configured client

```php
<?php

use InfluxDB\Client;
use InfluxDB\Options;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;

$options = new Options();
$options->setHost("analytics.mine.domain.tld");
$options->setPort(8086);
$options->setUsername("root");
$options->setPassword("root");
$options->setDatabase("mine");

$guzzleHttp = new GuzzleHttpClient();
$adapter = new InfluxHttpAdapter($guzzleHttp, $options);

$influx = new Client();
$influx->setAdapter($adapter);

$influx->mark("tcp.test", ["mark" => "element"]);
```

