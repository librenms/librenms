# Enabling support for InfluxDB

InfluxDB support is alpha quality. It only sends the data to a InfluxDB install.
InfluxDB changes often, so we cannot guarantee the integrity of your data.
Use this support at your own risk.

## Requirements

- InfluxDB >= 0.94 < 2.0
- Grafana

This document does not describe the setup of these components. We
cannot help with them.

## What you do not get

- Graphs. You need Grafana for this reason. Build your own graphs in
  Grafana.
- Support for InfluxDB or Grafana. You need experience with these
  tools.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

## Configuration

!!! setting "poller/influxdb"
    ```bash
    lnms config:set influxdb.enable true
    lnms config:set influxdb.transport http
    lnms config:set influxdb.host '127.0.0.1'
    lnms config:set influxdb.port 8086
    lnms config:set influxdb.db 'librenms'
    lnms config:set influxdb.username 'admin'
    lnms config:set influxdb.password 'admin'
    lnms config:set influxdb.timeout 0
    lnms config:set influxdb.batch_size 0
    lnms config:set influxdb.measurements ''
    lnms config:set influxdb.verifySSL false
    lnms config:set influxdb.debug false
    ```

Without InfluxDB authentication, no credentials are necessary.

LibreNMS sends the same data from rrd to InfluxDB, and InfluxDB records it.
You can then create graphs in Grafana for the information that you
need.
