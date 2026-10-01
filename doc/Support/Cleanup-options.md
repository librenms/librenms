# Cleanup Options

The number of devices in your LibreNMS install increases with time. The
RRD files and the MySQL database also become larger. The database holds
the eventlog, the syslog, and the performance data. A large install
therefore needs a cleanup of these entries. The cleanup options give
you this control.

These options need `daily.sh` in cron, as in the installation
instructions.

!!! setting "system/cleanup"
    ```bash
    lnms config:set eventlog_purge 30
    lnms config:set syslog_purge 30
    lnms config:set route_purge 10
    lnms config:set alert_log_purge 365
    lnms config:set authlog_purge 30
    lnms config:set ports_fdb_purge 10
    lnms config:set ports_nac_purge 10
    lnms config:set rrd_purge 0
    lnms config:set ports_purge true
    lnms config:set networks_purge true
    ```

These options purge data that is more than X days old. You can change
each option on its own. Each value is a day count.

**NOTE**: `rrd_purge` is NOT set by default. This option removes each
RRD file that had no update for the set number of days. Enable this
option only when you accept this behaviour. LibreNMS updates all active
RRD files in each polling period.

!!! note
    `rrd_purge` does not work over rrdcached. The rrd folder must be
    available on the local file system or on a remote file share. This
    condition also applies to docker and Kubernetes.

## Ports Purge

You add devices with time. Some interfaces then need a purge, because
they are ignored, bad, or marked as deleted.

To purge all deleted ports, use the web interface or set
`lnms config:set ports_purge true`.

In the web interface, open the Ports tab in the navigation bar. Click
"Deleted", then click "Purge all deleted". This action purges all the
ports.

## Networks Purge

If you add and remove subnets, the database can hold subnets without IP
addresses. The `networks_purge` option removes these unused networks
from the database.

