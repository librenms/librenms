# Dispatcher Service

The LibreNMS dispatcher service (`librenms-service.py`) is a method
of running the poller service at set times. It does not replace the php scripts,
just the cron entries running them.

The Dispatcher Service includes scheduling for the following processes:
 - Discovery
 - Poller
 - Services
 - Alerting
 - Billing
 - [Fast Ping](#fast-ping)
 - Daily Maintenance

## Setup

### Service Installation

A systemd unit file can be found in `misc/librenms.service`. You must adapt `ExecStart`
and `WorkingDirectory` if you did not install librenms in `/opt/librenms`

To install run:
```bash
cp /opt/librenms/misc/librenms.service /etc/systemd/system/librenms.service && systemctl enable --now librenms.service
```

### Disable Cron Scripts

To prevent to regular cron based poller from running, you need to disable the cron scripts.

You should remove all cron entries from `dist/librenms.cron`, or simply run:

```bash
rm /etc/cron.d/librenms
```

>Note: If you are using the librenms-scheduler cron instead of systemd timer, do not disable that.

### Validate

Let the poller run for a few minutes, then run `./validate.php` to check for known issues.

## Configuration

Configuration of the dispatcher can be done by two methods:
 - The Web UI
 - Configuration dafaults

### Web UI

In the web ui under `Settings` > `Poller` > `Settings`, you can configure each node.
Nodes will appear in this list after they have run for a few minutes.
To apply settings, you must restart the service of the affected node.

### Configuration Defaults

!!! setting "poller/dispatcherservice"
    ```bash
    lnms config:set service_poller_workers 24
    lnms config:set service_services_workers 8
    lnms config:set service_discovery_workers 16
    lnms config:set service_poller_frequency 300
    lnms config:set service_services_frequency 300
    lnms config:set service_discovery_frequency 21600
    lnms config:set service_billing_frequency 300
    lnms config:set service_billing_calculate_frequency 60
    lnms config:set service_poller_down_retry 60
    lnms config:set service_loglevel INFO
    lnms config:set service_update_frequency 86400
    ```

### Ensure only dispatcher runs processes

!!! setting "poller/dispatcherservice"
    ```bash
    lnms config:set schedule_type.poller dispatcher
    lnms config:set schedule_type.services dispatcher
    lnms config:set schedule_type.discovery dispatcher
    lnms config:set schedule_type.alerting dispatcher
    lnms config:set schedule_type.billing dispatcher
    ```

### Local Settings

Most of the time these settings are not needed, but if you want to set them,
they should only be set in `config.php`:

```php
$config['distributed_poller_name']  = php_uname('n');  # Uniquely identifies the poller instance
$config['distributed_poller_group'] = 0;               # Which group(s) to poll
```

## Optimization

### Worker Count

Some statistics from your dispatcher service are displayed in the `Settings` > `Poller` > `Poller` page.

>It is very important that you set the number of workers correctly. Too low and you
>will not finish polling in time and too high and you will overload your hardware.

You want to keep Consumed Worker Seconds comfortably below Maximum Worker Seconds (WS). The closer the values are to each 
other, the flatter the CPU graph of the poller machine. Meaning that you are utilizing your CPU resources well. As 
long as Consumed WS stays below Maximum WS and Devices Pending is 0, you should be ok.

If Consumed WS is below Maximum WS and Devices Pending is > 0, you may need to utilize [distributed polling](Distributed-Poller.md).

Maximum WS equals the number of workers multiplied with the number of seconds in the polling period. (default 300)

!!! warning "Workers"
The number of workers configured will be evenly split amongst the number of poller
groups configured. I.e if you have 4 groups and 24 workers then each group will get
6 workers. If you have an uneven distribution of devices between the groups then you
should consider setting the workers value higher.

### Performance Tuning

To get the most out of your hardware, please review the [Performance Documentation](../Support/Performance.md)

### Distributed Polling

A single instance will be able to poll as many as 1000 devices or even more, depending on a number of
variables such as latency and device speed.  If you have reached the limit of what you can
achieve with a single instance, you should consider using [distributed polling](Distributed-Poller.md).

## Fast Ping

The [fast ping](Fast-Ping-Check.md) scheduler is disabled by default.
Ensuring it is enabled in the poller settings and setting the following:

!!! setting "poller/scheduledtasks"
    ```bash
    lnms config:set schedule_type.ping dispatcher
    ```

## SystemD Service Watchdog

This service file is an alternative to the regular service file. It uses the systemd WatchdogSec= option 
to restart the service if it does not receive a keep-alive from the running process.

A systemd unit file can be found in `misc/librenms-watchdog.service`. To
install run:
```bash
cp /opt/librenms/misc/librenms-watchdog.service /etc/systemd/system/librenms.service && systemctl enable --now librenms.service
```

This requires: python3-systemd (or python-systemd on older systems)
or https://pypi.org/project/systemd-python/
If you run this systemd service without python3-systemd it will restart every 30 seconds.

