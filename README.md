# InfluxDB PHP SDK

 * [![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk)
 * [![Dependency Status](https://www.versioneye.com/user/projects/54104e789e1622492d000025/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54104e789e1622492d000025)

Send metrics to InfluxDB.

```php
$client = new \InfluxDB\Client();
$client->setAdapter(new \InfluxDB\Adapter\UdpAdapter());

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

## Prepare lib dependencies

Use your DiC or Service Locator in order to provide a configured client

```php
<?php

use InfluxDB\Client;
use InfluxDB\Options;
use InfluxDB\Adapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;

$options = new Options();
$options->setHost("analytics.mine.domain.tld");
$options->setPort(8086);
$options->setUsername("root");
$options->setPassword("root");

$guzzleHttp = new GuzzleHttpClient();
$adapter = new GuzzleAdapter($guzzleHttp, $options);
$adapter->setDatabase("mine");

$influx = new Client();
$influx->setAdapter($adapter);

$influx->mark("tcp.test", ["mark" => "element"]);
```

