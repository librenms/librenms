# InfluxDB PHP SDK
Send message to InfluxDB.

```php
$client = new \InfluxDB\Client();
$client->setAdapter(new \InfluxDB\Adapter\UdpAdapter());
$client->mark("search", [
    "query" => "php"
]);
```

