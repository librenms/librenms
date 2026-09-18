# Enabling support for OpenTSDB

This module sends all metrics to an OpenTSDB server. You need a tool
such as Grafana for the graphs.

## Requirements

- OpenTSDB
- Grafana

## What you do not get

 Graphs. You need Grafana for this reason. Build your own graphs in
 Grafana.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

Add these lines to your configuration:

## Configuration

!!! setting "poller/opentsdb"
    ```bash
    lnms config:set opentsdb.enable true
    lnms config:set opentsdb.host '127.0.0.1'
    lnms config:set opentsdb.port 4242
    ```

LibreNMS sends the same data from rrd to OpenTSDB, and OpenTSDB records
it. You can then create graphs in Grafana for the information that you
need.
