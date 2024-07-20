# Distributed Poller

A normal install contains all parts of LibreNMS:

- Poller/Discovery/etc workers
- RRD (Time series data store) *
- Database *
- Webserver (Web UI/API) *

\* may only be installed on one server (however, some can be clustered)

Distributed Polling allows the workers to be spread across additional
servers for horizontal scaling. Distributed polling is not intended for
remote polling.

Devices can be grouped together into a `poller_group` to pin these
devices to a single or a group of designated pollers.

All pollers need to write to the same set of RRD files, preferably via
RRDcached.

It is also a requirement that at least one locking service is in place
to which all pollers can connect. There are currently three locking
mechanisms available

- memcached
- redis (preferred)
- sql locks (default)

All of the above locking mechanisms are natively supported in LibreNMS.
If none are specified, it will default to using SQL.

## Requirements for distributed polling

These requirements are above the normal requirements for a full LibreNMS install.

- rrdtool version 1.4 or above
- At least one locking mechanism configured
- a rrdcached install

By default, all hosts are shared and have the `poller_group = 0`. To
pin a device to a poller, set it to a value greater than 0 and set the
same value in the poller's config with
`distributed_poller_group`. One can also specify a comma
separated string of poller groups in
`distributed_poller_group`.  The poller will then poll
devices from any of the groups listed.  If new devices get added from
the poller they will be assigned to the first poller group in the list
unless the group is specified when adding the device.

The following is a standard config, combined with a locking mechanism below:

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller true
    lnms config:set distributed_poller_group 0
    ```

If you want to customise the hostname for the poller then you will need
to set this in `config.php`:

```php
$config['distributed_poller_name']           = php_uname('n');
```

## Locking mechanisms
Pick one of the following setups, do not use all of them at the same
time.

### Using REDIS

In your `.env` file you will need to specify a redis server, port and
the driver.

```
REDIS_HOST=HOSTNAME or IP
REDIS_PORT=6379
CACHE_DRIVER=redis
```
### Using Memcached

Preferably you should set the memcached server settings via the web UI.
Under Settings > Global Settings > Distributed poller, you fill out the
memcached host and port, and then in your `.env` file you will need to add:

```
CACHE_DRIVER=memcached
```
If you want to use memcached, you will also need to install an additional
Python 3 python-memcached package.

## Example Setups

### OpenStack
Below is an example setup based on a real deployment which at the time
of writing covers over 2,500 devices and 50,000 ports. The setup is
running within an OpenStack environment with some commodity hardware
for remote pollers. Here's a diagram of how you can scale LibreNMS
out:

![OpenStack Example Setup](@= config.site_url =@/img/librenms-distributed-diagram-openstack.png)

### ESXi
This is a distributed setup that I created for a regional hybrid ISP 
(fixed wireless/fiber optic backhaul). It was created at around the 
~4,000 device mark to transition from multiple separate instances to one more central.
When I left the company, it was monitoring:
* 10,800 devices
* 307,700 ports
* 37,000 processors
* 17,000 wireless sensors
* ~480,000 other objects/sensors.

As our goal was more to catch alerts and monitor overall trends we went with a 10 minute polling cycle.
Polling the above would take roughly 8 minutes and 120GHz worth of CPU across all VMs. 
CPUs were older Xeons (E5). The diagram below shows the CPU and RAM utilization of each VM during polling.
Disk space utilization for SQL/RRD is also included.

Device discovery was split off into its own VM as that process would take multiple hours.



![ESXi Example Setup](@= config.site_url =@/img/librenms-distributed-diagram-esxi.png)

Workers were assigned in the following way:

* Web/RRD Server:
  * alerting: 1
  * billing: 2
  * discovery: 0
  * ping: 1
  * poller: 10
  * services: 16
* Discovery Server:
  * alerting: 1
  * billing: 2
  * discovery: 60
  * ping: 1
  * poller: 5
  * services: 8
* Pollers
  * alerting: 1
  * billing: 2
  * discovery: 0
  * ping: 1
  * poller: 40
  * services: 8

Each poller had on average 19,500/24,000 worker seconds consumed.

RRDCached is incredibly important; this setup ran on spinning disks
due to the wonders of caching.

I very strongly recommend setting up recursive DNS on your discovery 
and polling servers. While I used DNSMASQ there are many options.

SQL tuner will help you quite a bit. You'll also want to increase your 
maximum connections amount to support the pollers. This setup was at 500.
Less important, but putting ~12GB of the database in RAM was reported to 
have helped web UI performance as well as some DB-heavy Tableau reports.
RAM was precious in this environment or it would've been more, but it
wasn't necessary either.

Be careful with keeping the default value for 'Device Down Retry' as it
can eat up quite a lot of poller activity. I freed up over 20,000 worker seconds
when setting this to only happen once or twice per 10-minute polling cycle. 
The impact of this will vary depending on the percentage of down device in your system.
This example had it set at 400 seconds.

Also be wary of keeping event log and syslog entries for too long as it can
have a pretty negative effect on web UI performance.

To resolve an issue with large device groups the php fpm max input vars was increased to 20000.

All of these VMs were within the same physical data center so latency was minimal.

The decision of redis over the other locking methods was arbitrary but in over 2 years 
I never had to touch that VM aside from security updates.

This install used the service instead of cron.

## Architecture

How you set the distribution up is entirely up to you. You can choose
to host the majority of the required services on a single virtual
machine or server and then a poller to actually query the devices
being monitored, all the way through to having a dedicated server for
each of the individual roles. Below are notes on what you need to
consider both from the software layer, but also connectivity.

## Web / API Layer

This is typically Apache but we have setup guides for both Nginx and
Lighttpd which should work perfectly fine. There is nothing unique
about the role this service is providing except that if you are adding
devices from this layer then the web service will need to be able to
connect to the end device via SNMP and perform an ICMP test.

It is advisable to run RRDCached within this setup so that you don't
need to share the rrd folder via a remote file share such as NFS. The
web service can then generate rrd graphs via RRDCached. If RRDCached
isn't an option then you can mount the rrd directory to read the RRD
files directly.

## Database Server

MySQL / MariaDB - At the moment these are the only database servers
that are supported.

The pollers, web and API layers should all be able to access the
database server directly.

## RRD Storage

Central storage should be provided so all RRD files can be read from
and written to in one location. As suggested above, it's recommended
that RRD Cached is configured and used.

For this example, we are running RRDCached to allow all pollers and
web/api servers to read/write to the rrd files with the rrd directory
also exported by NFS for simple access and maintenance.

## Pollers

Pollers can be installed and run from anywhere, the only requirements are:

- They can access the Memcache instance
- They can create RRD files via some method such as a shared
  filesystem or RRDTool >=1.5.5
- They can access the MySQL server

You can either assign pollers into groups and set a poller group
against certain devices, this will mean that those devices will only
be processed by certain pollers (default poller group is 0) or you can
assign all pollers to the default poller group for them to process any
and all devices.

This will provide the ability to have a single poller behind a NAT
firewall monitor internal devices and report back to your central
system. You will then be able to monitor those devices from the Web UI
as normal.

Another benefit to this is that you can provide N+x pollers, i.e if
you know that you require three pollers to process all devices within
300 seconds then adding a 4th poller will mean that should any one
single poller fail then the remaining three will complete polling in
time. You could also use this to take a poller out of service for
maintenance, i.e OS updates and software updates.

It is extremely advisable to either run a central recursive dns server
such as pdns-recursor and have all of your pollers use this or install
a recursive dns server on each poller - the volume of DNS requests on
large installs can be significant and will slow polling down enough to
cause issues with a large number of devices.

A last note to make sure of, is that all pollers writing to the same DB
need to have the same `APP_KEY` value set in the `.env` file.

## Discovery

Depending on your setup will depend on how you configure your discovery processes.

**Cron based polling**

It's not necessary to run discovery services on all pollers. In fact, you should
only run one discovery process per poller group.
Designate a single poller to run discovery (or a separate server if required).

If you run billing, you can do this in one of two ways:

1. Run poll-billing.php and calculate-billing.php on a single poller which will 
create billing information for all bills. Please note this poller must have 
snmp access to all of your devices which have ports within a bill.
1. The other option is to enable `$config['distributed_billing'] = true;` in 
config.php. Then run poll-billing.php on a single poller per group. You can run 
calculate-billing.php on any poller but only one poller overall.

**Dispatcher service**
When using the dispatcher service, discovery can run on all nodes.

## Configuration

Settings in config.php should be copied to all servers as they only apply locally.

One way around this is to set settings in the database via the web ui or `./lnms config:set`

## Config sample

The following config is taken from a live setup which consists of a
Web server, DB server, RRDCached server and 3 pollers.

Web Server:

Running Apache and an install of LibreNMS in /opt/librenms

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller true
    ```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "example.com:42217"
    ```

Database Server:
Running Memcache and MariaDB

- Memcache

Ubuntu (/etc/memcached.conf)

```conf
-d
-m 64
-p 11211
-u memcache
-l ip.ip.ip.ip
```

RRDCached Server:
Running RRDCached

- RRDCached

Ubuntu (/etc/default/rrdcached)

```conf
OPTS="-l 0:42217"
OPTS="$OPTS -j /var/lib/rrdcached/journal/ -F"
OPTS="$OPTS -b /opt/librenms/rrd -B"
OPTS="$OPTS -w 1800 -z 900"
```

Ubuntu (/etc/default/rrdcached) - RRDCached 1.5.5 and above.

```
BASE_OPTIONS="-l 0:42217"
BASE_OPTIONS="$BASE_OPTIONS -R -j /var/lib/rrdcached/journal/ -F"
BASE_OPTIONS="$BASE_OPTIONS -b /opt/librenms/rrd -B"
BASE_OPTIONS="$BASE_OPTIONS -w 1800 -z 900"
```

Poller 1:
Running an install of LibreNMS in /opt/librenms

`config.php`

```php
$config['distributed_poller_name']           = php_uname('n');
$config['distributed_poller_group']          = '0';
$config['distributed_billing']               = true;
```

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller_memcached_host "example.com"
    lnms config:set distributed_poller_memcached_port 11211
    lnms config:set distributed_poller true
    ```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "example.com:42217"
    ```

`/etc/cron.d/librenms`

Runs discovery and polling for group 0, daily.sh to deal with
notifications and DB cleanup and alerts.

```conf
33   */6  * * *   librenms    /opt/librenms/cronic /opt/librenms/discovery-wrapper.py 1
*/5  *    * * *   librenms    /opt/librenms/discovery.php -h new >> /dev/null 2>&1
*/5  *    * * *   librenms    /opt/librenms/cronic /opt/librenms/poller-wrapper.py 16
15   0    * * *   librenms    /opt/librenms/daily.sh >> /dev/null 2>&1
*    *    * * *   librenms    /opt/librenms/alerts.php >> /dev/null 2>&1
```

Poller 2:
Running an install of LibreNMS in /opt/librenms

`config.php`

```php
$config['distributed_poller_name']           = php_uname('n');
$config['distributed_poller_group']          = '0';
$config['distributed_billing']               = true;
```

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller_memcached_host "example.com"
    lnms config:set distributed_poller_memcached_port 11211
    lnms config:set distributed_poller true
    ```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "example.com:42217"
    ```

`/etc/cron.d/librenms`

Runs billing as well as polling for group 0.

```conf
*/5 * * * * librenms /opt/librenms/poller-wrapper.py 16 >> /opt/librenms/logs/wrapper.log
*/5 * * * * librenms /opt/librenms/poll-billing.php >> /dev/null 2>&1
01  * * * * librenms /opt/librenms/billing-calculate.php >> /dev/null 2>&1
15  0 * * * librenms    /opt/librenms/daily.sh >> /dev/null 2>&1
```

Poller 3:
Running an install of LibreNMS in /opt/librenms

`config.php`

```php
$config['distributed_poller_name']           = php_uname('n');
$config['distributed_poller_group']          = '2,3';
$config['distributed_billing']               = true;
```

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller_memcached_host "example.com"
    lnms config:set distributed_poller_memcached_port 11211
    lnms config:set distributed_poller true
    ```

!!! setting "poller/rrdtool"
    ```bash
    lnms config:set rrdcached "example.com:42217"
    ```

`/etc/cron.d/librenms`
Runs discovery and polling for groups 2 and 3.

```conf
33  */6 * * *   librenms    /opt/librenms/cronic /opt/librenms/discovery-wrapper.py 1
*/5 *   * * *   librenms    /opt/librenms/discovery.php -h new >> /dev/null 2>&1
*/5 *   * * *   librenms    /opt/librenms/poll-billing.php >> /dev/null 2>&1
*/5 *   * * *   librenms    /opt/librenms/cronic /opt/librenms/poller-wrapper.py 16
15  0   * * *   librenms    /opt/librenms/daily.sh >> /dev/null 2>&1
```
