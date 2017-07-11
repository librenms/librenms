source: Extensions/InfluxDB.md
# Enabling support for OpenTSDB.

This module sends all metrics to OpenTSDB server. You need something like Grafana for graphing.

### Requirements
 - OpenTSDB
 - Grafana
 
### What you don't get
 - Pretty graphs, this is why at present you need Grafana. You need to build your own graphs within Grafana.
 
RRD will continue to function as normal so LibreNMS itself should continue to function as normal.

for configuration in /opt/librenms/includes/defaults.inc.php.
### Configuration
```php
// OpenTSDB default configuration
$config['opentsdb']['enable'] = true;
$config['opentsdb']['host'] = '127.0.0.1';  // your OpenTSDB server
$config['opentsdb']['port'] = 4242;
$config['opentsdb']['co'] = true;  // if you want to suffix your metric by Customer identity or object code
```

You can use the field 'co' to suffix your metrics if required, and Update the co field in your database with the list of your corresponding Customer and your metrics will be in the following syntax'net.measurement.co timestamps value hostname tags' else put it false and you will have this 'net.measurement timestamps value hostname tags'.

No credentials are needed if you don't use InfluxDB authentication.

The same data then stored within rrd will be sent to OpenTSDB and recorded. You can then create graphs within Grafana
to display the information you need.
