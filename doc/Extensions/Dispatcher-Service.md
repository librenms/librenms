# Dispatcher Service

The **LibreNMS Dispatcher Service** (`librenms-service.py`) schedules core LibreNMS tasks without using cron jobs.
It does **not** replace the PHP scripts - only the cron entries that run them.

The Dispatcher handles scheduling for:

 - Discovery
 - Poller
 - Services
 - Alerting
 - Billing
 - [Fast Ping](#fast-ping)
 - Daily Maintenance

---

## Setup

### Install the Service

A systemd unit file is provided in `misc/librenms.service`.
Update `ExecStart` and `WorkingDirectory` if LibreNMS is not installed in `/opt/librenms`.

```bash
cp /opt/librenms/misc/librenms.service /etc/systemd/system/librenms.service && systemctl enable --now librenms.service
```

### Disable Cron Jobs

To prevent duplicate polling, disable existing cron-based pollers:

```bash
rm /etc/cron.d/librenms
```

> **Note:** If using the *librenms-scheduler* cron (systemd timer), do **not** disable it.

### Validate Setup

Let the poller run for a few minutes, then verify with:

```bash
./validate.php
```

---

## Configuration

The dispatcher can be configured through:

* The **Web UI**
* **Configuration defaults**

>Note: Settings are not applied until you restart the service.

### Web UI

In the Web UI, go to **Settings > Poller > Settings** to manage node configurations.
Nodes appear automatically after running for a few minutes.

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

### Restrict Processing to Dispatcher

!!! setting "poller/dispatcherservice"
    ```bash
    lnms config:set schedule_type.poller dispatcher
    lnms config:set schedule_type.services dispatcher
    lnms config:set schedule_type.discovery dispatcher
    lnms config:set schedule_type.alerting dispatcher
    lnms config:set schedule_type.billing dispatcher
    ```

### Local Settings

Optional settings - define only in `config.php`:

```php
$config['distributed_poller_name']  = php_uname('n');  // Unique poller name
$config['distributed_poller_group'] = 0;               // Poller group ID
```

---

## Optimization

### Worker Count

Dispatcher statistics appear in **Settings > Poller > Poller**.

> **Tip:** Set the number of workers carefully.
> Too few will delay polling; too many will overload your hardware.

Keep **Consumed Worker Seconds** below **Maximum Worker Seconds**.
If the two values are close and **Devices Pending** is 0, the poller is well tuned.
If **Devices Pending** > 0 while **Consumed Worker Seconds** < **Maximum Worker Seconds**,
consider [Distributed Polling](Distributed-Poller.md).

**Maximum WS** = `workers × polling interval (default 300s)`

!!! warning "Workers"
    The configured workers are divided evenly across poller groups.
    For example, 24 workers and 4 groups = 6 workers per group.
    If device distribution is uneven, increase worker count or rebalance groups.

### Performance Tuning

See [Performance Documentation](../Support/Performance.md) for advanced tuning.

### Distributed Polling

A single instance can poll up to **1,000+ devices**, depending on latency and device responsiveness.
If performance limits are reached, use [Distributed Polling](Distributed-Poller.md).

---

## Fast Ping

The [Fast Ping](Fast-Ping-Check.md) scheduler is disabled by default.
If you use it, enable Fast Ping in the poller settings and set the following:

!!! setting "poller/scheduledtasks"
    ```bash
    lnms config:set schedule_type.ping dispatcher
    ```

## Systemd Watchdog Service

An alternate service file uses `WatchdogSec` to restart the dispatcher if it becomes unresponsive.

To install:

```bash
cp /opt/librenms/misc/librenms-watchdog.service /etc/systemd/system/librenms.service && systemctl enable --now librenms.service
```

Requires **python3-systemd** (or **python-systemd** on older systems).
Without it, the service restarts every 30 seconds.
