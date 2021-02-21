source: Support/Cleanup-options.md
path: blob/master/doc/

# Cleanup Options

As the number of devices starts to grow in your LibreNMS install, so
will things such as the RRD files, MySQL database containing
eventlogs, Syslogs and performance data etc. Your LibreNMS install
could become quite large so it becomes necessary to clean up those
entries. With Cleanup Options, you can stay in control.

These options rely on ```daily.sh``` running from cron as per the installation instructions.

Cleanup Options are set in ```config.php```

```php
$config['syslog_purge']                              = 30;
$config['eventlog_purge']                            = 30;
$config['authlog_purge']                             = 30;
$config['device_perf_purge']                         = 7;
$config['rrd_purge']                                 = 90;// Not set by default
$config['ports_purge']                               = true;// Set to false by default
```

These options will ensure data within LibreNMS over X days old is
automatically purged. You can alter these individually, values are in
days.

**NOTE**: Please be aware that ```$config['rrd_purge']``` is NOT set
by default. This option will remove any RRD files that have not been
updated for the set amount of days automatically - only enable this if
you are comfortable with that happening. (All active RRD files are
updated every polling period.)

# Ports Purge

Over time as you add devices some interfaces will need to be purged as
they are set to be ignored or bad interfaces or marked as deleted.

You can purge all deleted ports from the WebUI (see below) or by
setting `$config['ports_purge'] = true;` in `config.php`

In the Web UI Under the Ports Tab in the Nav Bar, Click on "Deleted"
then click on "Purge all deleted". This will purge all the ports.
