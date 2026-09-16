# Enabling support for InfluxDBv2

InfluxDBv2 support is alpha quality. It only sends the data to a InfluxDBv2 bucket.
InfluxDB changes often, so we cannot guarantee the integrity of your data.
Use this support at your own risk.

InfluxDBv2 supports only the InfluxDBv2 API of InfluxDB version 2.0 and
later. For any other InfluxDB version, use the InfluxDB datastore.

## Requirements

- InfluxDB >= 2.0

This document does not describe the setup of these components. We
cannot help with them.

## What you do not get

- Support for InfluxDB. You need experience with this tool.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

## Configuration

!!! setting "poller/influxdbv2"
    ```bash
    lnms config:set influxdbv2.enable true
    lnms config:set influxdbv2.transport http
    lnms config:set influxdbv2.host '127.0.0.1'
    lnms config:set influxdbv2.port 8086
    lnms config:set influxdbv2.bucket 'librenms'
    lnms config:set influxdbv2.token 'admin'
    lnms config:set influxdbv2.allow_redirect true
    lnms config:set influxdbv2.organization 'librenms'
    lnms config:set influxdbv2.debug false
    lnms config:set influxdbv2.log_file '/opt/librenms/logs/influxdbv2.log'
    lnms config:set influxdbv2.groups-exclude ["group_name_1","group_name_2"]
    lnms config:set influxdbv2.timeout 5
    lnms config:set influxdbv2.verify false
    lnms config:set influxdbv2.batch_size 1000
    lnms config:set influxdbv2.max_retry 2
    ```

LibreNMS sends the same data from rrd to InfluxDB, and InfluxDB records
it. You can then create graphs in Grafana or in InfluxDB for the
information that you need.

Note: the polling becomes slower when the poller cannot reach
InfluxDBv2 or cannot write data to it.
