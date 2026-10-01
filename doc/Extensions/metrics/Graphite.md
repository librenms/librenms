# Enabling support for Graphite

This module sends all metrics to a remote Graphite service. You need a
tool such as Grafana for the graphs.

## What you do not get

- Graphs. You need Grafana for this reason. Build your own graphs in
  Grafana.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

## Configuration

!!! setting "poller/graphite"
    ```bash
    lnms config:set graphite.enable true
    lnms config:set graphite.host 'your.graphite.server'
    lnms config:set graphite.port 2003
    lnms config:set graphite.prefix 'your.metric.prefix'
    ```

You can add a prefix to your metric path. Without a prefix, the
Graphite metric path has the form `hostname.measurement.fieldname`.
Graphite stores an interface as `hostname.ports.ifName.fieldname`.

LibreNMS sends the same data from rrd to Graphite, and Graphite records it.
You can then create graphs in Grafana for the information that you
need.

## Graphite Configuration

LibreNMS updates its metrics every 5 minutes. We therefore recommend
this addition to your `storage-schemas.conf`.

```
[network]
pattern = your\.metric\.prefix\..*
retentions = 5m:30d,15m:90d,1h:1y
```
