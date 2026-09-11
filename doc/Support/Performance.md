# Performance optimisations

This document gives guidance on the optimisation of your setup.

The list starts with the changes that have the largest effect.

## RRDCached

**We recommend [RRDCached](../Extensions/RRDCached.md). It reduces the IO load.**

## MySQL Optimisation

After 24 hours of MySQL operation, run [MySQL
Tuner](https://raw.githubusercontent.com/major/MySQLTuner-perl/master/mysqltuner.pl).
This tool recommends changes for your own setup.

We recommend this setting in `my.cnf`, under a `[mysqld]` group:

```bash
innodb_flush_log_at_trx_commit = 0
```

You can also set this option to 2. A crash of MySQL or of the server
can then lose up to 1 second of MySQL data. In exchange, the IO use
decreases a lot.

## Polling modules

Open gear > pollers > performance. The graph of the poller module time
shows which modules use the poller time. The same data is available per
device under device > graphs > poller.

Disable the polling modules and the discovery modules that you do not
need. To disable a module globally, use
`lnms config:set poller_module.<module>`.

To disable OSPF polling, use this command:

!!! setting "poller/poller_modules"
    ```bash
    lnms config:set poller_modules.ospf false
    ```

You can disable a module globally, then enable it for one device. You
can also do the opposite. For a list of the modules, read [Poller
modules](../Support/Poller%20Support.md).

## SNMP Max Repeaters

LibreNMS supports SNMP max repeaters. This option helps on devices with
many ports or many BGP sessions, where LibreNMS uses `snmpwalk` or
`snmpbulkwalk`. Enable the option for one device under
edit device -> snmp -> Max repeaters.

You can also set the option globally:

!!! setting "poller/snmp"
    ```bash
    lnms config:set snmp.max_repeaters X
    ```

To find the best value, measure the time of an `snmpwalk` over IF-MIB.
Run the command below with different values in place of `-REPEATERS-`,
from 10 to about 50. Also set the correct SNMP version, hostname, and
community string:

```bash
time snmpbulkwalk -v2c -cpublic HOSTNAME -Cr-REPEATERS- -M /opt/librenms/mibs -m IF-MIB IfEntry
```

!!! warning
    Measure the result before you set this value. A wrong value makes
    the polling worse.

## SNMP Max OIDs

For sensor polling, LibreNMS does bulk `snmpget` requests. This makes
the polling faster. The default value is ten. You can change the value
for one device under edit device -> snmp -> Max OIDs.

You can also set the option globally:

!!! setting "poller/snmp"
    ```bash
    lnms config:set snmp.max_oid X
    ```

!!! warning
    After a change, monitor the sensor polling. The value can be too
    high.

## fping tuning

You can change the default fping options globally or for one device.
These are the defaults:

!!! setting "poller/ping"
    ```bash
    lnms config:set fping_options.timeout 500
    lnms config:set fping_options.count 3
    lnms config:set fping_options.interval 500
    ```

If your devices answer slowly, increase the timeout value. You can also
increase the interval value. If your network is stable, you can improve
the poller performance. Decrease the count value to 1, the timeout
value to 200 or 300, or both:

!!! setting "poller/ping"
    ```bash
    lnms config:set fping_options.timeout 300
    lnms config:set fping_options.count 1
    lnms config:set fping_options.interval 300
    ```

LibreNMS then sends no delay of 0.5 seconds between the ICMP packets.
By default, LibreNMS sends 3 packets in total. With only 1 ICMP packet,
the response arrives faster. With the defaults, a response takes at
least 1 second, even for a fast device.

## Optimise poller-wrapper

The default for `poller-wrapper.py` is 16 threads. This value is not
always optimal. A common rule is 2 threads for each core. Increase and
decrease the number until you find the optimal value.

!!! note
    This change does not always help. The result depends on your system
    and your CPU. The value is in the cron job for librenms, usually in
    `/etc/cron.d/librenms`. Change the number "16" in that file.

```
*/5  *    * * *   librenms    /opt/librenms/cronic /opt/librenms/poller-wrapper.py 16
```

If you use the Dispatcher Service, you can set the number of threads in
the web interface. For more information, read [Dispatcher
Service](../Extensions/Dispatcher-Service.md).

## Recursive DNS

If your install uses hostnames for many devices, install a local
recursive DNS instance on the LibreNMS server. You can use
pdns-recursor. Then configure `/etc/resolv.conf` to send queries to
127.0.0.1.

## Per port polling

By default, the ports polling module walks ifXEntry and some items from
ifEntry. It does this walk for every port, at every port status.
LibreNMS therefore collects data for a deleted port and for a disabled
port. The walks are fast, so this behaviour is usually acceptable. It
is not optimal on a device with many ports where a large percentage is
deleted or disabled. For such a device, enable "selected port polling"
under edit device -> misc. You can also enable it globally, but we do
**not** recommend this:

!!! setting "poller/ports"
    ```bash
    lnms config:set polling.selected_ports true
    ```

We do not recommend the global setting, because it increases the CPU
use of your poller. You can also set the option for one OS:

!!! setting "poller/ports"
    ```bash
    lnms config:set os.ios.polling.selected_ports true
    ```

Run `./scripts/collect-port-polling.php` as the `librenms` user. The
script polls your devices with full polling and with selective polling.
It then shows a table with the difference. It can also enable or
disable selected ports polling for the devices with a benefit. The
script does not repeat this analysis. It updates the setting only at
each run. These options are available:

```bash
-h <device id> | <device hostname wildcard>  Poll single device or wildcard hostname
-e <percentage>                              Enable/disable selected ports polling for devices which would benefit <percentage> from a change
```

To set selected port polling on the devices with a change of **10% or
more**, run `./scripts/collect-port-polling.php -e 10`. The script uses
a second condition. The change in the polling time must also be more
than one second.

## Web interface

### HTTP/2

If you use HTTPS, enable HTTP/2 support in your web server:

For Nginx 1.9.5 and later, change `listen 443 ssl;` to
`listen 443 ssl http2;` in the virtual host configuration.

For Apache 2.4.17 and later, set `Protocols h2 http/1.1` in the virtual
host configuration.

## PHP-opcache

A correct `php-opcache` configuration gives a large gain in
performance.

**Note: memory based caching with the PHP command line increases the
memory use and makes the system slower. File based caching is slower
than memory based caching, and stale cache problems are more common.**

Some distributions have separate configurations for the command line,
`mod_php`, and `php-fpm`. Use these to set the optimal configuration.

### For web servers using mod_php and php-fpm

Update the `opcache.ini` file of your web PHP. The possible locations
are `/etc/php/8.3/fpm/conf.d/opcache.ini`, `/etc/php.d/opcache.ini`,
and `/etc/php/conf.d/opcache.ini`.

```ini
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=256
```

If you have a caching problem, restart `httpd` or `php-fpm`. This
clears the opcache.

### For pollers

First create a cache directory. The `librenms` user must have write
access to it:
`sudo mkdir -p /tmp/cache && sudo chmod 775 /tmp/cache && sudo chown -R librenms /tmp/cache`

Update your `opcache.ini` file. The possible locations are
`/etc/php/8.3/cli/conf.d/opcache.ini`, `/etc/php.d/opcache.ini`, and
`/etc/php/conf.d/opcache.ini`.

```ini
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1
opcache.file_cache="/tmp/cache/"
opcache.file_cache_only=0
opcache.file_cache_consistency_checks=1
opcache.memory_consumption=256
```

If you have a caching problem, clear the file based opcache with
`rm -rf /tmp/cache`.

On Debian 12, PHP 8.2.7 causes segmentation faults when opcache uses
the file cache. The cause is this problem:
https://github.com/php/php-src/issues/10914
To correct it, use the sury packages or disable the file cache.
