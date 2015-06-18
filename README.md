# influxdb-php
## InfluxDB client library for PHP

###Overview

This library was created to have php port of the python influxdb client. 
This way there will be a common abstraction library between different programming languages.

###Usage

Initialize a new client object:

```php

$client = new Leaseweb\InfluxDB\Client($host, $port);


```

This will create a new client object which you can use to read and write points to InfluxDB.
