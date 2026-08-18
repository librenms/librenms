# 1-Minute Polling

LibreNMS can poll data at the interval that you select.

> Read these conditions first:

- For faster up and down alerts, [Fast
  Ping](../Extensions/Fast-Ping-Check.md) is a much easier method.
- If you change the interval from the default of 300 seconds, you must
  also change the cron entry for `poller-wrapper.py`.
- The polling _MUST_ complete within the heartbeat step value. Open
  `/poller` in your web interface to see the current value.
- The change applies only to RRD files that LibreNMS creates after the
  change.
- The change applies to all storage mechanisms, such as MySQL, RRD, and
  InfluxDB. A lower value increases the disk space for MySQL and
  InfluxDB.
- Configure some [performance optimizations](Performance.md). Each
  device has many graphs, and each device writes all its graphs to the
  disk every minute. The [RRDCached](../Extensions/RRDCached.md)
  configuration is the most important one, because it saves many write
  IOPS.

To make the change, open `/settings/poller/rrdtool/` in your web
interface. Select RRDTool Setup. Then set the two values for the step
interval and the heartbeat interval:

- Step is the interval between two data inserts. For 1-minute polling,
  set this value to 60.
- Heartbeat is the time to wait for data before LibreNMS records a null
  value. An example value is 120 seconds.

## Converting existing RRD files

We supply a basic script. This script converts the default RRD files to
your step value and your heartbeat value. Back up your RRD files before
you run the script. The script runs for one device or for all devices.

> The RRD files must be available on the server that runs this script.

`lnms maintenance:rrd-step`

This command shows the help information. To run the script for
localhost, run:

`lnms maintenance:rrd-step localhost`
