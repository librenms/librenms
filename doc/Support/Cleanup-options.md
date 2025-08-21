# Cleanup Options

As the number of devices starts to grow in your LibreNMS install, so
will things such as the RRD files, MySQL database containing
eventlogs, Syslogs and performance data etc. Your LibreNMS install
could become quite large so it becomes necessary to clean up those
entries. With Cleanup Options, you can stay in control.

These options rely on ```daily.sh``` running from cron as per the installation instructions.

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

These options will ensure data within LibreNMS over X days old is
automatically purged. You can alter these individually, values are in
days.

**NOTE**: Please be aware that `rrd_purge` is NOT set
by default. This option will remove any RRD files that have not been
updated for the set amount of days automatically - only enable this if
you are comfortable with that happening. (All active RRD files are
updated every polling period.)

## Ports Purge

Over time as you add devices some interfaces will need to be purged as
they are set to be ignored or bad interfaces or marked as deleted.

You can purge all deleted ports from the WebUI (see below) or by
setting `lnms config:set ports_purge true`.

In the Web UI Under the Ports Tab in the Nav Bar, Click on "Deleted"
then click on "Purge all deleted". This will purge all the ports.

## Networks Purge

If you add and remove subnets, over time you may end up with subnets in
the database that no longer have any IP addresses attached to them. These
unused networks can be removed from the database if you enable the 
networks_purge option.

