source: Support/Performance.md
path: blob/master/doc/

# Performance optimisations

This document will give you some guidance on optimising your setup.

The suggestions are in a rough order of how much impact they will have.

## RRDCached

**We absolutely recommend running this, it will save on IO load**. [RRDCached](../Extensions/RRDCached.md)

## MySQL Optimisation

It's advisable after 24 hours of running MySQL that you run [MySQL
Tuner](https://raw.githubusercontent.com/major/MySQLTuner-perl/master/mysqltuner.pl)
which will make suggestions on things you can change specific to your setup.

One recommendation we can make is that you set the following in my.cnf
under a [mysqld] group:

```bash
innodb_flush_log_at_trx_commit = 0
```

You can also set this to 2. This will have the possibility that you
could lose up to 1 second on mysql data in the event MySQL crashes or
your server does but it provides an amazing difference in IO use.

## Polling modules

Review the graph of poller module time take under gear > pollers >
performance to see what modules are consuming poller time. This data
is shown per device under device > graphs > poller.

Disable polling (and discovery) modules that you do not need. You can
do this globally in `config.php` like:

Disable OSPF polling

```php
$config['poller_modules']['ospf'] = false;
```

You can disable modules globally then re-enable the module per device
or the opposite way. For a list of modules please see [Poller
modules](../Support/Poller%20Support.md)

## SNMP Max Repeaters

We have support for SNMP Max repeaters which can be handy on devices
where we poll a lot of ports or bgp sessions for instance and
where snmpwalk or snmpbulkwalk is used. This needs to be enabled on a
per device basis under edit device -> snmp -> Max repeaters.

You can also set this globally with the config option
`$config['snmp']['max_repeaters'] = X;`.

It's advisable to test the time taken to snmpwalk IF-MIB or something
similar to work out what the best value is. To do this run the
following but replace -REPEATERS- with varying numbers from 10 upto
around 50. You will also need to set the correct snmp version,
hostname and community string:

`time snmpbulkwalk -v2c -cpublic HOSTNAME -Cr-REPEATERS- -M
/opt/librenms/mibs -m IF-MIB IfEntry`

> NOTE: Do not go blindly setting this value as you can impact polling
> negatively.

## SNMP Max OIDs

For sensors polling we now do bulk snmp gets to speed things up. By
default this is ten but you can overwrite this per device under edit
device -> snmp -> Max OIDs.

You can also set this globally with the config option
`$config['snmp']['max_oid'] = X;`.

> NOTE: It is advisable to monitor sensor polling when you change this
> to ensure you don't set the value too high.

## fping tuning

You can change some of the default fping options used globally or per
device. The defaults are:

```php
$config['fping_options']['timeout'] = 500;
$config['fping_options']['count']   = 3;
$config['fping_options']['interval'] = 500;
```

If your devices are slow to respond then you will need to increase the
timeout value and potentially the interval value. However if your
network is stable, you can increase poller performance by dropping the
count value to 1 and/or the timeout+millsec value to 200 or 300:

```php
$config['fping_options']['timeout'] = 300;
$config['fping_options']['count']   = 1;
$config['fping_options']['interval'] = 300;
```

This will mean that we no longer delay each icmp packet sent (we send
3 in total by default) by 0.5 seconds. With only 1 icmp packet
being sent then we will receive a response quicker. The defaults mean
it will take at least 1 second for a response no matter how
quick the icmp packet is returned.

## Optimise poller-wrapper

The default 16 threads that `poller-wrapper.py` runs as isn't
necessarily the optimal number. A general rule of thumb is 2 threads
per core but we suggest that you play around with lowering /
increasing the number until you get the optimal value. **Note** KEEP
in MIND that this doesn't always help, it depends on your system and
CPU. So be careful. This can be changed by going to the cron job for
librenms. Usually in `/etc/cron.d/librenms` and changing the "16"

```
*/5  *    * * *   librenms    /opt/librenms/cronic /opt/librenms/poller-wrapper.py 16
```
Please also see [Dispatcher Service](../Extensions/Dispatcher-Service.md)

## Recursive DNS

If your install uses hostnames for devices and you have quite a lot
then it's advisable to setup a local recursive dns instance on the
LibreNMS server. Something like pdns-recursor can be used and then
configure `/etc/resolv.conf` to use 127.0.0.1 for queries.

## Per port polling - experimental

By default the polling ports module will walk ifXEntry + some items
from ifEntry regardless of the port. So if a port is marked as deleted
because you don't want to see them or it's disabled then we still
collect data. For the most part this is fine as the walks are quite
quick. However for devices with a lot of ports and good % of those are
either deleted or disabled then this approach isn't optimal. So to
counter this you can enable 'selected port polling' per device within
the edit device -> misc section or by globally enabling it (**not
recommended**): `$config['polling']['selected_ports'] = true;`.  
This is truly not recommended, as it has been proven to affect cpu
usage of your poller negatively. You can also set it for a specific OS:
`$config['os']['ios']['polling']['selected_ports'] = true;`.

Running `./scripts/collect-port-polling.php` will poll your devices
with both full and selective polling, display a table with the
difference and optionally enable or disable selected ports polling for
devices which would benefit from a change. Note that it doesn't
continuously re-evaluate this, it will only be updated when the script
is run. There are a number of options:

```
-h <device id> | <device hostname wildcard>  Poll single device or wildcard hostname
-e <percentage>                              Enable/disable selected ports polling for devices which would benefit <percentage> from a change
```
If you want to run this script to have it set selected port polling
on devices where a change of **10% or more is evaluated**, run it with
`./scripts/collect-port-polling.php -e 10`. But note: it will not
blindly use only the 10%. There is a second condition that the change
has to be more than one second in polling time.

## Web interface

### HTTP/2

If you are running https then you should enable http/2 support in
whatever web server you use:

For Nginx (1.9.5 and above) change `listen 443 ssl;` to `listen 443
ssl http2;` in the Virtualhost config.

For Apache (2.4.17 an above) set `Protocols h2 http/1.1` in the Virtualhost config.

## PHP-opcache

A lot of performance can be gained from setting up `php-opcache` correctly.

**Note: Memory based caching with PHP cli will increase memory usage and slow things down. File based caching is not as fast as memory based and is more likely to have stale cache issues.**

Some distributions allow separate cli, mod_php and php-fpm configurations, we can use this to set the optimal config.

### For web servers using mod_php and php-fpm

Update your web PHP opcache.ini.  Possible locations: `/etc/php/7.2/fpm/conf.d/opcache.ini`, `/etc/php.d/opcache.ini`, or `/etc/php/conf.d/opcache.ini`.

```
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=256
```

If you are having caching issues, you can clear the opcache by simply restarting httpd or php-fpm.

### For pollers

Create a cache directory that is writable by the librenms user first:
`sudo mkdir -p /tmp/cache && sudo chmod 775 /tmp/cache && sudo chown -R librenms /tmp/cache`

Update your PHP opcache.ini.  Possible locations: `/etc/php/7.2/cli/conf.d/opcache.ini`, `/etc/php.d/opcache.ini`, or `/etc/php/conf.d/opcache.ini`.

```
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1
opcache.file_cache="/tmp/cache/"
opcache.file_cache_only=0
opcache.file_cache_consistency_checks=1
opcache.memory_consumption=256
```

If you are having caching issues, you can clear the file based opcache with `rm -rf /tmp/cache`.


