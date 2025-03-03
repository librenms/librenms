# Enabling support for Graphite

This module sends all metrics to a remote graphite service. You need
something like Grafana for graphing.

## What you don't get

- Pretty graphs, this is why at present you need Grafana. You need to
  build your own graphs within Grafana.

RRD will continue to function as normal so LibreNMS itself should
continue to function as normal.

## Configuration

!!! setting "poller/graphite"
    ```bash
    lnms config:set graphite.enable true
    lnms config:set graphite.host 'your.graphite.server'
    lnms config:set graphite.port 2003
    lnms config:set graphite.prefix 'your.metric.prefix'
    ```

Your metric path can be prefixed if required, otherwise the metric
path for Graphite will be in the form of
`hostname.measurement.fieldname`, interfaces will be stored as
`hostname.ports.ifName.fieldname`.

The same data then stored within rrd will be sent to Graphite and
recorded. You can then create graphs within Grafana to display the
information you need.

## Graphite Configuration

As LibreNMS updates its metrics every 5 minutes, the following
addition to your storage-schemas.conf is suggested.

```
[network]
pattern = your\.metric\.prefix\..*
retentions = 5m:30d,15m:90d,1h:1y
```
