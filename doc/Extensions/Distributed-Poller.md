# Distributed Poller
LibreNMS has the ability to distribute polling of devices to other machines.

These machines can be in a different physical location and therefore minimize network latencies for colocations.

Devices can also be groupped together into a `poller_group` to pin these devices to a single or a group of designated pollers.

All pollers need to share their RRD-folder, for example via NFS or a combination of NFS and rrdcached.  
It is also required that all pollers can access the central memcached to communicate with eachother.

In order to enable distributed polling, set `$config['distributed_poller'] = true` and your memcached details into `$config['distributed_poller_memcached_host']` and `$config['distributed_poller_memcached_port']`.  
By default, all hosts are shared and have the `poller_group = 0`. To pin a device to a poller, set it to a value greater than 0 and set the same value in the poller's config with `$config['distributed_poller_group']`.  
Usually the poller's name is equal to the machine's hostname, if you want to change it set `$config['distributed_poller_name']`.

__Note__: Eventhough you pin devices to pollers, these pollers will still poll devices with `poller_group = 0`. If you do not want this, consequently define groups for all your devices!

## Configuration
```php
// Distributed Poller-Settings
$config['distributed_poller']                            = false;
$config['distributed_poller_name']                       = file_get_contents('/proc/sys/kernel/hostname');
$config['distributed_poller_group']                      = 0;
$config['distributed_poller_memcached_host']             = 'example.net';
$config['distributed_poller_memcached_port']             = '11211';
```
