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

## Create the database

Before enabling the datastore, create the database on your InfluxDB server.
LibreNMS writes to the configured database but does not check for or create it.
Existing installations with the database already present need no changes.

Run this using the InfluxDB CLI, authenticating as an administrator if required:

```sql
CREATE DATABASE "librenms"
```

If authentication is enabled, grant the LibreNMS user write access to that
database. Database administration privileges are not required for LibreNMS.
Use the same database name in `influxdb.db` below.

For a fresh official InfluxDB 1.8 Docker container, set `INFLUXDB_DB=librenms`
on the InfluxDB container before its first startup. Initialization variables
only apply to a new data directory; for an existing volume, create the database
using the CLI instead. See the [InfluxDB Docker initialization documentation](https://docs.influxdata.com/influxdb/v1/introduction/install/docker/#initialize-influxdb).

When sending metrics through a write-only relay such as Telegraf, provision the
destination database on the backing InfluxDB server rather than the relay.

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
