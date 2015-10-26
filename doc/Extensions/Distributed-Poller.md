# Distributed Poller
LibreNMS has the ability to distribute polling of devices to other machines.

These machines can be in a different physical location and therefore minimize network latencies for colocations.

Devices can also be groupped together into a `poller_group` to pin these devices to a single or a group of designated pollers.

~~All pollers need to share their RRD-folder, for example via NFS or a combination of NFS and rrdcached.~~

> This is no longer a strict requirement with the use of rrdtool 1.5 and above. If you are NOT running 1.5 then you will still 
need to share the RRD-folder.

It is also required that all pollers can access the central memcached to communicate with eachother.

In order to enable distributed polling, set `$config['distributed_poller'] = true` and your memcached details into `$config['distributed_poller_memcached_host']` and `$config['distributed_poller_memcached_port']`.  
By default, all hosts are shared and have the `poller_group = 0`. To pin a device to a poller, set it to a value greater than 0 and set the same value in the poller's config with `$config['distributed_poller_group']`.  
Usually the poller's name is equal to the machine's hostname, if you want to change it set `$config['distributed_poller_name']`.
One can also specify a comma seperated string of poller groups in $config['distributed_poller_group'].  The poller will then poll devices from any of the groups listed.  If new devices get added from the poller they will be assigned to the first poller group in the list unless the group is specified when adding the device.

## Configuration
```php
// Distributed Poller-Settings
$config['distributed_poller']                            = false;
$config['distributed_poller_name']                       = file_get_contents('/proc/sys/kernel/hostname');
$config['distributed_poller_group']                      = 0;
$config['distributed_poller_memcached_host']             = 'example.net';
$config['distributed_poller_memcached_port']             = '11211';
```

## Example Setup
Below is an example setup based on a real deployment which at the time of writing covers over 2,500 devices and 50,000 ports. The setup is running within an Openstack environment with some commodity ha
rdware for remote pollers. Here's a diagram of how you can scale LibreNMS out:

![Example Setup](http://docs.librenms.org/img/librenms-distributed-diagram.png)

###Architecture
How you setup the distribution is entirely up to you, you can choose to host the majority of the required services on a single virtual machine or server and then a poller to actually query the devices being monitored all the way through to having a dedicated server for each of the individual roles. Below are notes on what you need to consider both from the software layer but also connectivity.

####Web / API Layer
This is typically Apache but we have setup guides for both Nginx and Lighttpd which should work perfectly fine. There is nothing unique about the role this service is providing except that if you are adding devices from this layer then the web service will need to be able to connect to the end device via SNMP and perform an ICMP test.

It is advisable to run RRDCached within this setup so that you don't need to share the rrd folder via a remote file share such as NFS. The web service can then generate rrd graphs via RRDCached. If RRDCached isn't an option then you can mount the rrd directory to read the RRD files directly.

We would recommend that you run some form of php caching application such as PHP XCache

The MySQL server should be contactable from this layer on port 3306 unless it's changed.

####Database Server
MySQL - At the moment this is the only databse server that is supported, work is being done to ensure MySQL Strict mode is also supported but this should be considered to be incomplete still.

The pollers, web and API layers should all be able to access the database server directly. It would be possible to configure MySQL multi master but that is outside the scope of this document.

####RRD Storage
Central storage should be provided so all RRD files can be read from and written to in one location. As suggested above, it's recommended that RRD Cached is configured and used.

For this example, we are running RRDCached to allow all pollers and web/api servers to read/write to the rrd iles ~~with the rrd directory also exported by NFS for simple access and maintenance.~~

Sharing rrd files via something like NFS is no longer required if you run rrdtool 1.5 or greater. If you don't - please share your rrd folder as before. If you run rrdtool 
1.5 or greater then add this config to your pollers:

```php
$config['rrdtool_version'] = 1.5;
```

####Memcache
Memcache is required for the distributed pollers to be able to register to a central location and record what devices are polled. Memcache can run from any of the kit so long as it is accessable by all pollers.

####Pollers
Pollers can be installed and run from anywhere, the only requirements are:

They can access the Memcache instance
They can create RRD files via some method such as a shared filesystem
They can access the MySQL server

You can either assign pollers into groups and set a poller group against certain devices, this will mean that those devices will only be processed by certain pollers (default poller group is 0) or you can assign all pollers to the default poller group for them to process any and all devices.

This will provide the ability to have a single poller behind a NAT firewall monitor internal devices and report back to your central system. You will then be able to monitor those devices from the Web UI as normal.

Another benefit to this is that you can provide N+x pollers, i.e if you know that you require three pollers to process all devices within 300 seconds then adding a 4th poller will mean that should any one single poller fail then the remaining three will complete polling in time. You could also use this to take a poller out of service for maintenance, i.e OS updates and software updates.

It is exctremely advisable to either run a central recursive dns server such as pdns-recursor and have all of your pollers use this or install a recursive dns server on each poller - the volume of DNS requests on large installs can be significant.

####Discovery
It's not necessary to run discovery services on all pollers. In fact, you should only run one discovery process per poller group. Designate a single poller to run discovery (or a seperate server if required).

####Config sample
Memcache:

 - This doesn't require any special config. The example setup is running "-m 64 -p 11211 -l <ip>"

RRDCached:

 - You will need to tune RRDCached to suite your environment.
 - The following is used in this example setup "-l 0:42217 -j /var/lib/rrdcached/journal/ -F -b /opt/librenms/rrd -B -w 1800 -z 900 -p /var/run/rrdcached.pid"

```php
$config['rrdcached']    = "127.0.0.1:42217";
$config['rrd_dir']      = "/opt/librenms/rrd";
$config['rrdcached_dir'] = "";
```

For rrdtool 1.5 or greater then you can enable support for rrdcached to create the rrd files:

```php
$config['rrdtool_version'] = 1.5;
```

$config['rrdcached_dir'] Is only needed if you are using tcp connections for rrd cached and needs only to be set if you want to store rrd files within a sub directory of your rrdcached base directory.
