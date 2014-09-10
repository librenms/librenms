# InfluxDB PHP SDK
[![Build Status](https://travis-ci.org/corley/influxdb-php-sdk.svg?branch=master)](https://travis-ci.org/corley/influxdb-php-sdk) [![Dependency Status](https://www.versioneye.com/user/projects/54104e789e1622492d000025/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54104e789e1622492d000025)
Send message to InfluxDB.

```php
$client = new \InfluxDB\Client();
$client->setAdapter(new \InfluxDB\Adapter\UdpAdapter());
$client->mark("search", [
    "query" => "php"
]);
```

