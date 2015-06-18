Using LibreNMS Daemon
---------------------

## Abstract

LibreNMS consists of several independent jobs that can be run either as cronjob or using the shipped daemon `librenmsd`.

Cronjobs often have a penalty in accurancy so when adding a fixed interval in an alert, it might be skipped because be cronjob triggered the job some secs earlier than in the previous cycle. Although this can be countered by adjusting the Alert's tolerance-window, it's generally not a good thing to have jobs triggered at irregular cycles when the backend expects it to be as accurate and regular as possible.

As mentioned with the Alerts, also RRD has a fixed step of 5 minutes and a tolerance of about +/-10 secs.

Irregularities within your RRDs may have a more severe impact on the NMS as it may lead to invalid datasets and thus be ignored in graphing. This is bad.

These reasons, although not exclusively, made the need of an accurate daemon to replace the cron-daemon in order to gain precise cycles.

## Running the Daemon

### Running it through cron

We recommend running the daemon through cron to have watchdog capabilities. This way the cron will attempt to restart the daemon in case it exists unexpectedly.

```crontab
*    *    * * *   root    /opt/librenms/librenmsd start >> /dev/null 2>&1
```

### Running it as init-script

On most systems, you can simply symlink `/etc/init.d/librenmd` to `/opt/librenms/librenmsd`.
The daemon comes with LSB Compliant headers, on a debian system you would issue `insserv librenmsd` or similar to autogenerate the runlevel links.

On a RHEL/Centos system prior to 7, you need to issue `chkconfig --add librenmsd`.

On a distribution using systemd (RHEL/Centos 7 or later) you'll need to create a `librenmsd.service` file yourself and put it in the correct directory.
Here is a skelleton:
```systemd
[Unit]
Description=LibreNMS Daemon
After=syslog.target

[Service]
ExecStart=/opt/librenmsd foreground

[Install]
WantedBy=multi-user.target
```

#### In case you do not run librenms in /opt/librenms, you're `REQUIRED` to adjust the `$BASEDIR` varible in the top of the `librenmsd` file.

## Job configuration

The Daemon reloads the `config.php` on changes.

Currently each interval is 10s. Although it is possible to go lower, it's discouraged to do so in order to avoid unecessarly loads.

The config auto-detects when Distributed Polling is set up and will automatically exclude jobs for alerts, services and billing unless told not to in order to avoid redundant work across the pollers. See `Notes on Distributed Polling` for cofig options.


### Job Object

Each Job is an array in form of `array('type'=>%TYPE, 'file'=>%FILE, 'args'=>%ARGS)`.

`%TYPE` can be either `include` or `exec`. If set to `include`, the daemon will include the file specified. If set to `exec` it will execute the file within a subshell.

`%FILE` is relative to the install-directory specified in the `config.php`.

`%ARGS` is optional and will only be passed to jobs of type `exec`.

### Intervals Object

The Intervals Object is an 3 dimensional array. Each dimension is a positive number.

| Dimension | Usage                 |
| --------- | --------------------- |
| 1         | Base in seconds.      |
| 2         | Units of Base.        |
| 3         | Order to execute Job. |

In other words, if you want to run a job every 7 minutes, use `[60][7][] = $MyJob;`.
As you can see, the last dimension is empty. Unless you know what you're doing, there's no reason to specify it, it's more likely that you will overwrite a previous job by accident.

Although you can define each job by the base-unit of 1, we discourage this. It's more effective when defaulting to the next highest base-unit.

Defaults:
```php
$config['daemon']['intervals'][60][2][]    = array('type'=>'exec',    'file'=>'discovery.php',     'args'=>'-h new'); // Discover new devices every 2 minutes
$config['daemon']['intervals'][60][5][]    = array('type'=>'exec',    'file'=>'poller-wrapper.py', 'args'=>'16');     // Poller runs every 5 minutes
$config['daemon']['intervals'][3600][6][]  = array('type'=>'exec',    'file'=>'discovery.php',     'args'=>'-h all'); // Re-Discover every 6 Hours
$config['daemon']['intervals'][86400][1][] = array('type'=>'exec',    'file'=>'daily.sh');                            // Daily at midnight. Interval overflows here, everything over [86400][1] remains daily.
```

## Notes on Distributed Polling

When running in a distributed setup, the default behavior is to exclude the non-distributable jobs for _Dispatching Alerts_, _Calculating Bills_ and _Check Services_.

It's recommended to only allow one poller to execute those 3 jobs, best would be to let the GUI-Machine do it.

```php
$config['daemon']['run']['alerts']   = true; // Run alerts    although in a distributed setup
$config['daemon']['run']['billing']  = true; // ..  billing   ..
$config['daemon']['run']['services'] = true; // ..  servvices ..
```

## Daemon Config

The Daemon will log to your syslog. The default Facility is `LOG_DAEMON`, Debug-Statements will go to `LOG_DEBUG` regardless of the `facility`-settings.

```php
$config['daemon']['facility'] = LOG_DAEMON;                        // Log-facility.
$config['daemon']['debug']    = false;                             // Debug, General Enable/Disable (true/false) or Enable specific sections by names.
$config['daemon']['uid']      = posix_getpwnam('librenms')['uid']; // UID to use for daemon.
```

## Debugging!

#### Run in foreground

```shell
/opt/librenms/librenmsd foreground
```

#### Add a sections to debug

`$config['daemon']['debug'] = 'jobctl,clock,main';`

#### Transform Interval to Time

`rTimestamp  = ( Interval * Step )`

This returns the number of seconds from 00:00:00.

#### How to calculate from Dimensions to Trigger

`Trigger     = ( Base * Units ) / Step`

A Job triggers when `Interval % Trigger` equals `0`

