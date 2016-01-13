# Enabling support for InfluxDB.

Before we get started it is important that you know and understand that InfluxDB support is currently alpha at best. 
All it provides is the sending of data to a InfluxDB install. Due to the current changes that are constantly being 
made to InfluxDB itself then we cannot guarantee that your data will be ok so enabling this support is at your own 
risk!

### Requirements
 - InfluxDB 0.94
 - Grafana

The setup of the above is completely out of scope here and we aren't really able to provide any help with this side 
of things.

### What you don't get
 - Pretty graphs, this is why at present you need Grafana. You need to build your own graphs within Grafana.
 - Support for InfluxDB or Grafana, we would highly recommend that you have some level of experience with these.

RRD will continue to function as normal so LibreNMS itself should continue to function as normal.

### Configuration
```php
$config['influxdb']['enable'] = true;
$config['influxdb']['transport'] = 'http';
$config['influxdb']['host'] = '127.0.0.1';
$config['influxdb']['port'] = '8086';
$config['influxdb']['db'] = 'librenms';
$config['influxdb']['username'] = 'admin';
$config['influxdb']['password'] = 'admin';
```

UDP is a supported transport and no credentials are needed if you don't use InfluxDB authentication.

The same data then stored within rrd will be sent to InfluxDB and recorded. You can then create graphs within Grafana 
to display the information you need.
