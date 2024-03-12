# Enabling support for InfluxDBv2

Before we get started it is important that you know and understand
that InfluxDBv2 support is currently alpha at best. All it provides is
the sending of data to a InfluxDBv2 bucket. Due to the current changes
that are constantly being made to InfluxDB itself then we cannot
guarantee that your data will be ok so enabling this support is at
your own risk!

It is also important to understand that InfluxDBv2 only supports the
InfluxDBv2 API used in InfluxDB version 2.0 or higher. If you are
looking to send data to any other version of InfluxDB than you should
use the InfluxDB datastore instead.

## Requirements

- InfluxDB >= 2.0

The setup of the above is completely out of scope here and we aren't
really able to provide any help with this side of things.

## What you don't get

- Support for InfluxDB, we would highly recommend that you
  have some level of experience with these.

RRD will continue to function as normal so LibreNMS itself should
continue to function as normal.

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
    lmns config:set influxdbv2.organization 'librenms'
    ```

The same data stored within rrd will be sent to InfluxDB and
recorded. You can then create graphs within Grafana or InfluxDB to display the
information you need.
