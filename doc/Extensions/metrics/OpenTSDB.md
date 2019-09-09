source: Extensions/metrics/OpenTSDB.md
path: blob/master/doc/

# Enabling support for OpenTSDB

This module sends all metrics to OpenTSDB server. You need something
like Grafana for graphing.

# Requirements

- OpenTSDB
- Grafana

# What you don't get

 Pretty graphs, this is why at present you need Grafana. You need to
 build your own graphs within Grafana.

RRD will continue to function normally so LibreNMS itself should
continue to function normally.

You can add the following to `config.php`.

# Configuration

```php
// OpenTSDB default configuration
$config['opentsdb']['enable'] = true;
$config['opentsdb']['host'] = '127.0.0.1';  // your OpenTSDB server
$config['opentsdb']['port'] = 4242;
```

The same data than the one stored within rrd will be sent to OpenTSDB
and recorded. You can then create graphs within Grafana to display the
information you need.
