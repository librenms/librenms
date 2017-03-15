source: Extensions/Graphite.md
# Enabling support for Graphite.

This module sends all metrics to a remote graphite service. You need something like Grafana for graphing.

### What you don't get
 - Pretty graphs, this is why at present you need Grafana. You need to build your own graphs within Grafana.

RRD will continue to function as normal so LibreNMS itself should continue to function as normal.

### Configuration
```php
$config['graphite']['enable'] = true;
$config['graphite']['host'] = 'your.graphite.server';
$config['graphite']['port'] = 2003; // this defaults to 2003 and is usually not needed
$config['graphite']['prefix'] = 'your.metric.prefix';
```

Your metric path can be prefixed if required, otherwise the metric path for Graphite will be in the form of
`hostname.measurement.fieldname`

The same data then stored within rrd will be sent to Graphite and recorded. You can then create graphs within Grafana
to display the information you need.
