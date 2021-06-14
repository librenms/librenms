source: Support/1-Minute-Polling.md
path: blob/master/doc/

# Information

We now have support for polling data at intervals to fit your needs.

> Please be aware of the following:

- If you just want faster up/down alerts, [Fast Ping](../Extensions/Fast-Ping-Check.md) is a much
  easier path to that goal.
- You must also change your cron entry for `poller-wrapper.py` for
  this to work (if you change from the default 300 seconds).
- Your polling _MUST_ complete in the time you configure for the
  heartbeat step value. See `/poller` in your WebUI for
  your current value.
- This will only affect RRD files created from the moment you change
  your settings.
- This change will affect all data storage mechanisms such as MySQL,
  RRD and InfluxDB. If you decrease the values then please be aware of
  the increase in space use for MySQL and InfluxDB.
- It's **highly recommended** to configure some [performance
  optimizations](Performance.md). Keep in mind that all your devices
  will write all graphs every minute to the disk and that every device
  has many graphs. The most important thing is probably the
  [RRDCached](../Extensions/RRDCached.md) configuration that can save
  a lot of write IOPS.

To make the changes, please navigate to `/settings/poller/rrdtool/`
within your WebUI. Select RRDTool Setup and then update the two values
for step and heartbeat intervals:

- Step is how often you want to insert data, so if you change to 1
  minute polling then this should be 60.
- Heartbeat is how long to wait for data before registering a null
  value, i.e 120 seconds.

# Converting existing RRD files

We provide a basic script to convert the default rrd files we generate
to utilise your configured step and heartbeat values. Please do ensure
that you backup your RRD files before running this just in case. The
script runs on a per device basis or all devices at once.

> The rrd files must be accessible from the server you run this script from.

`./scripts/rrdstep.php`

This will provide the help information. To run it for localhost just run:

`./scripts/rrdstep.php -h localhost`
