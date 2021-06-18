source: Extensions/Fast-Ping-Check.md
path: blob/master/doc/

# Fast up/down checking

Normally, LibreNMS sends an ICMP ping to the device before polling to
check if it is up or down. This check is tied to the poller frequency,
which is normally 5 minutes.  This means it may take up to 5 minutes
to find out if a device is down.

Some users may want to know if devices stop responding to ping more
quickly than that. LibreNMS offers a ping.php script to run ping
checks as quickly as possible without increasing snmp load on your
devices by switching to 1 minute polling.

**WARNING**: If you do not have an alert rule that alerts on device
status, enabling this will be a waste of resources. You can find one
in the [Alert Rules
Collection](../Alerting/Rules.md#alert-rules-collection).

## Setting the ping check to 1 minute

1: If you are using [RRDCached](../Extensions/RRDCached.md), stop the service.

    - This will flush all pending writes so that the rrdstep.php script can change the steps.

2: Change the ping_rrd_step setting in config.php

```
$config['ping_rrd_step'] = 60;
```

3: Update the rrd files to change the step (step is hardcoded at file
creation in rrd files)

```
./scripts/rrdstep.php -h all
```

4: Add the following line to /etc/cron.d/librenms to allow 1 minute
ping checks

```
*    *    * * *   librenms    /opt/librenms/ping.php >> /dev/null 2>&1
```

5: If applicable: Start the [RRDCached](../Extensions/RRDCached.md) service


**NOTE**: If you are using distributed pollers you can restrict a
poller to a group by appending `-g` to the cron entry.  Alternatively,
you should only run ping.php on a single node.

## Sub minute ping check

Cron only has a resolution of one minute, so for sub-minute ping checks we need to adapt both `ping`
and `alerts` entries. We add two entries per function, but add a delay before one of these entries.

Remember, you need to remove the original `ping.php` and `alerts.php` entries in crontab before
proceeding!

1: Set ping_rrd_step

```
$config['ping_rrd_step'] = 30;
```

2: Update the rrd files

```
./scripts/rrdstep.php -h all
```

3: Update cron (removing any other ping.php or alert.php entries)

```
*    *    * * *   librenms    /opt/librenms/ping.php >> /dev/null 2>&1
*    *    * * *   librenms    sleep 30 && /opt/librenms/ping.php >> /dev/null 2>&1
*    *    * * *   librenms    sleep 15 && /opt/librenms/alerts.php >> /dev/null 2>&1
*    *    * * *   librenms    sleep 45 && /opt/librenms/alerts.php >> /dev/null 2>&1
```

# Device dependencies

The ping.php script respects device dependencies, but the main poller
does not (for technical reasons). However, using this script does not
disable the icmp check in the poller and a child may be reported as
down before the parent.

# Settings

`ping.php` uses much the same settings as the poller fping with one
exception: retries is used instead of count.
`ping.php` does not measure loss and avg response time, only up/down, so
once a device responds it stops pinging it.

```
$config['fping_options']['retries'] = 2;
$config['fping_options']['timeout'] = 500;
$config['fping_options']['interval'] = 500;
```
