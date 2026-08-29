# Fast up/down checking

By default, LibreNMS sends an ICMP ping to a device before the poll.
This ping tests the up state or the down state. The check uses the
poller frequency, usually 5 minutes. A down device therefore takes up
to 5 minutes to appear.

Some users need a faster report of a device without a ping response.
The `ping.php` script runs the ping checks as fast as possible. It does
not increase the SNMP load of 1-minute polling on your devices.

!!! warning

    Fast Ping checks need a device down alert rule. The [Alert Rules
    Collection](../Alerting/Rules.md#alert-rules-collection) holds one.

## Setting the ping check to 1 minute

To run the fast pings with the dispatcher service:

!!! setting "poller/rrdtool"

    ```bash
    lnms config:set schedule_type.ping dispatcher
    lnms config:set service_ping_frequency 60
    systemctl restart librenms.service
    ```

With cron:

```title="/etc/cron.d/librenms"
*    *    * * *   librenms    /opt/librenms/ping.php >> /dev/null 2>&1
```

!!! note

    With distributed pollers, limit a poller to a group. Add `-g` to the
    cron entry. You can also run `ping.php` on only one node.

## Device dependencies

The `ping.php` script obeys the device dependencies. For technical
reasons, the main poller does not. This script does not disable the
ICMP check of the poller. A child device can therefore appear as down
before its parent.

## Settings

`ping.php` uses almost the same settings as the poller fping. There is
one difference: it uses `retries` in place of `count`.
`ping.php` measures only the up state and the down state. It does not
measure the loss or the average response time. It stops the ping of a
device after the first response.

!!! setting "poller/ping"

    ```bash
    lnms config:set fping_options.retries 2
    lnms config:set fping_options.timeout 500
    lnms config:set fping_options.interval 500
    ```
